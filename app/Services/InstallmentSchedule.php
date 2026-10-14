<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;

class InstallmentSchedule
{
    public function forCustomer(Customer $customer): array
    {
        $customer->loadMissing(['piutangs', 'kavling', 'pemasukans']);
        $contract = max(0, (int) $customer->piutangs->sum('nominal'));
        $paid = max(0, (int) $customer->piutangs->sum('terbayar'));
        $remaining = max(0, $contract - $paid);
        $today = Carbon::today('Asia/Jakarta');
        $rows = []; $total = 0;
        $add = function (int $month, string $label, int $amount, ?Carbon $date) use (&$rows, &$total, $paid, $today) {
            if ($amount <= 0) return;
            $allocated = min($amount, max(0, $paid - $total));
            $total += $amount;
            $balance = $amount - $allocated;
            $status = $balance === 0 ? 'Lunas' : ($allocated > 0 ? 'Sebagian' : ($date && $date->lt($today) ? 'Terlambat' : ($date && $date->isSameDay($today) ? 'Hari ini' : 'Belum dibayar')));
            $rows[] = ['month' => $month, 'label' => $label, 'date' => $date, 'amount' => $amount, 'paid' => $allocated,
                'balance' => $balance, 'cumulative' => $total, 'pay_through' => max(0, $total - $paid),
                'status' => $status, 'late' => $balance > 0 && $date && $date->lt($today)];
        };
        $firstDate = $customer->kavling->pluck('pivot.tgl_terima')->filter()->sort()->first();
        $transactionDate = $this->date($firstDate);
        $valid = true;
        if ($customer->jenis_pembelian === 'Kredit') {
            $tenor = (int) $customer->inhouse_tenor;
            $monthly = (int) $customer->inhouse_perbulan;
            $start = $this->date($customer->inhouse_jatuh_tempo);
            $valid = $tenor >= 1 && $tenor <= 600 && $monthly > 0 && $start !== null;
            $deposit = min($contract, max(0, (int) ($customer->dp_kredit ?? max(0, $contract - $tenor * $monthly))));
            $add(0, 'Uang muka (DP)', $deposit, $transactionDate);
            if ($valid) {
                for ($month = 1; $month <= $tenor; $month++) {
                    $amount = $month === $tenor ? max(0, $contract - $total) : min($monthly, max(0, $contract - $total));
                    $add($month, 'Cicilan ke-'.$month, $amount, $start->copy()->addMonthsNoOverflow($month - 1));
                }
            }
        } else {
            if ($customer->jenis_pembelian === 'Booking') $add(0, 'Pembayaran booking', min($contract, max(0, (int) $customer->pembayaran_booking)), $transactionDate);
            $add(1, $customer->jenis_pembelian === 'Booking' ? 'Sisa pelunasan kavling' : 'Pelunasan kavling', max(0, $contract - $total), $this->date($customer->tgl_batas_booking) ?? $transactionDate);
        }
        return ['contract' => $contract, 'paid' => $paid, 'remaining' => $remaining, 'rows' => collect($rows), 'valid' => $valid,
            'percent' => $contract > 0 ? min(100, round($paid / $contract * 100, 1)) : 0,
            'due' => collect($rows)->filter(fn ($row) => $row['date'] && $row['date']->lte($today))->sum('balance')];
    }

    private function date(?string $value): ?Carbon
    {
        try {
            $text = substr((string) $value, 0, 10);
            $date = Carbon::createFromFormat('!Y-m-d', $text, 'Asia/Jakarta');
            return $date && $date->year >= 1900 && $date->toDateString() === $text ? $date : null;
        } catch (\Throwable) {return null;}
    }
}
