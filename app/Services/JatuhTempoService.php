<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JatuhTempoService
{
    private ?Collection $cachedRows = null;

    public function rows(): Collection
    {
        if ($this->cachedRows !== null) return $this->cachedRows;
        $today = Carbon::today('Asia/Jakarta');
        $customers = Customer::where('stt_arsip', 0)
            ->where('inhouse_perbulan', '>', 0)->where('inhouse_tenor', '>', 0)
            ->whereNotNull('inhouse_jatuh_tempo')->where('inhouse_jatuh_tempo', '!=', '0000-00-00')
            ->whereDate('inhouse_jatuh_tempo', '<=', $today->toDateString())
            ->with(['kavling' => fn ($query) => $query->select('kavling_peta.id', 'id_lokasi', 'kode_kavling', 'hrg_jual'), 'kavling.lokasi:id,nama_kavling'])
            ->withSum('piutangs', 'nominal')
            ->withSum(['pemasukans' => fn ($query) => $query->where(fn ($q) => $q->whereNull('keterangan')->orWhere('keterangan', '!=', 'GANTI NAMA'))], 'nominal')
            ->get();
        $rows = collect();
        foreach ($customers as $customer) {
            $schedule = $this->calculate($customer, $today);
            if (!$schedule) continue;
            $phone = preg_replace('/\D/', '', $customer->no_wa ?: $customer->no_telp ?: '');
            if (str_starts_with($phone, '0')) $phone = '62'.substr($phone, 1);
            elseif (str_starts_with($phone, '8')) $phone = '62'.$phone;
            $phone = strlen($phone) >= 9 && strlen($phone) <= 15 ? $phone : '';
            $codes = $customer->kavling->pluck('kode_kavling')->filter()->implode(', ');
            $locations = $customer->kavling->map(fn ($kavling) => $kavling->lokasi?->nama_kavling)->filter()->unique()->implode(', ');
            $message = 'Halo '.$customer->nama_lengkap.', kami ingin mengingatkan pembayaran cicilan kavling '.($codes ?: 'Anda')
                .' yang jatuh tempo pada '.$schedule['due_date']->locale('id')->translatedFormat('d F Y')
                .'. Jumlah yang sudah jatuh tempo Rp '.number_format($schedule['due_amount'], 0, ',', '.')
                .'. Silakan konfirmasi jika pembayaran sudah dilakukan. Terima kasih.';
            $rows->push($schedule + [
                'id' => $customer->id, 'name' => $customer->nama_lengkap,
                'codes' => $codes ?: '—', 'locations' => $locations ?: '—',
                'phone' => $customer->no_wa ?: $customer->no_telp,
                'whatsapp_url' => $phone ? 'https://wa.me/'.$phone.'?text='.rawurlencode($message) : null,
            ]);
        }
        return $this->cachedRows = $rows->sortBy('due_date')->values();
    }

    private function calculate(Customer $customer, Carbon $today): ?array
    {
        try {
            $dateText = substr((string) $customer->inhouse_jatuh_tempo, 0, 10);
            $start = Carbon::createFromFormat('!Y-m-d', $dateText, 'Asia/Jakarta');
            if (!$start || $start->format('Y-m-d') !== $dateText || $start->year < 1900 || $start->gt($today)) return null;
        } catch (\Throwable) {
            return null;
        }
        $monthly = (float) $customer->inhouse_perbulan;
        $tenor = (int) $customer->inhouse_tenor;
        $installmentsTotal = $monthly * $tenor;
        $price = $customer->kavling->sum(fn ($kavling) => (float) ($kavling->pivot->hrg_rumah ?? $kavling->hrg_jual ?? 0));
        $contract = (float) $customer->piutangs_sum_nominal;
        if ($contract <= 0) $contract = $price > 0 ? $price : $installmentsTotal;
        $paid = max(0, (float) $customer->pemasukans_sum_nominal);
        $remaining = max(0, $contract - $paid);
        if ($remaining <= 0) return null;
        // Payments first cover the amount outside the installment schedule (e.g. down payment).
        $deposit = $customer->dp_kredit !== null
            ? min($contract, max(0, (float) $customer->dp_kredit))
            : max(0, $contract - $installmentsTotal);
        $installmentsPaid = max(0, $paid - $deposit);
        $months = ($today->year - $start->year) * 12 + $today->month - $start->month;
        if ($start->copy()->addMonthsNoOverflow($months)->gt($today)) $months--;
        $dueInstallments = min($tenor, max(0, $months + 1));
        $dueAmount = $dueInstallments >= $tenor
            ? $remaining
            : min($remaining, max(0, $dueInstallments * $monthly - $installmentsPaid));
        if ($dueAmount < 0.01) return null;
        $firstUnpaidIndex = min($tenor - 1, (int) floor($installmentsPaid / $monthly));
        $dueDate = $start->copy()->addMonthsNoOverflow($firstUnpaidIndex);
        $lateDays = max(0, (int) $dueDate->diffInDays($today));
        return ['due_date' => $dueDate, 'late_days' => $lateDays, 'due_amount' => $dueAmount,
            'remaining' => $remaining, 'monthly' => $monthly,
            'status' => $lateDays > 0 ? 'Terlambat' : 'Hari ini'];
    }
}
