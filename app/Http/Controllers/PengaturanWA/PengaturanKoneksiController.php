<?php

namespace App\Http\Controllers\PengaturanWA;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\KonfigurasiWa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PengaturanKoneksiController extends Controller
{
    public function index()
    {
        $data        = KonfigurasiWa::first();
        $permissions = HakAksesController::getUserPermissions();

        return view('admin.pengaturan_wa.pengaturan_koneksi.index', compact('data', 'permissions'));
    }

    public function update(Request $request, $id = null)
    {
        $id = $request->input('id') ?: $id;
        $konfigurasi = $id ? KonfigurasiWa::find($id) : KonfigurasiWa::first();

        $rules = [
            'url_api'    => 'required|url',
            'api_key'    => 'required',
            'number_key' => 'required',
        ];

        $messages = [
            'url_api.required'    => 'URL API wajib diisi.',
            'url_api.url'         => 'URL API tidak valid.',
            'api_key.required'    => 'Api key wajib diisi.',
            'number_key.required' => 'Number key wajib diisi.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $payload = [
                'url_api'       => $request->url_api,
                'api_key'       => $request->api_key,
                'number_key'    => $request->number_key,
                'stt_keteangan' => $konfigurasi->stt_keteangan ?? 1,
            ];

            if ($konfigurasi) {
                $konfigurasi->update($payload);
            } else {
                KonfigurasiWa::create($payload);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan koneksi berhasil disimpan.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan koneksi gagal disimpan.',
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    public function kirimPesanWa(Request $request)
    {
        $request->validate([
            'no_wa'   => 'required',
            'message' => 'required',
        ], [
            'no_wa.required'   => 'Nomor WhatsApp wajib diisi.',
            'message.required' => 'Pesan wajib diisi.',
        ]);

        $kredensial = KonfigurasiWa::first();

        $clientId = $kredensial ? ($this->extractClientIdFromUrl($kredensial->url_api) ?: $kredensial->number_key) : null;

        if (! $kredensial || ! $kredensial->url_api || ! $kredensial->api_key || ! $clientId) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan koneksi WA belum lengkap.',
            ], 422);
        }

        $phoneNo = $this->normalizePhoneNumber($request->no_wa);

        $payload = [
            'clientId'   => $clientId,
            'to'         => $phoneNo,
            'text'       => $request->message,
            'api_key'    => $kredensial->api_key,
            'number_key' => $kredensial->number_key,
            'no_wa'      => $phoneNo,
            'phone_no'   => $phoneNo,
            'message'    => $request->message,
        ];

        try {
            $urlApi = $this->resolveTextApiUrl($kredensial->url_api);
            $hasUrlPlaceholder = str_contains($urlApi, '{');

            if ($hasUrlPlaceholder) {
                $replace = [];
                foreach ($payload as $key => $value) {
                    $replace['{' . $key . '}'] = rawurlencode((string) $value);
                }

                $urlApi = strtr($urlApi, $replace);

                $response = Http::timeout(30)->acceptJson()->get($urlApi);
            } else {
                $response = Http::timeout(30)->acceptJson()->post($urlApi, $payload);
            }

            $responseData = $response->json();
            $apiStatus = strtolower((string) data_get($responseData, 'status', ''));
            $apiSuccessValue = data_get($responseData, 'success', true);

            $apiMessage = data_get($responseData, 'message')
                ?? data_get($responseData, 'detail')
                ?? data_get($responseData, 'reason')
                ?? data_get($responseData, 'error')
                ?? data_get($responseData, 'msg')
                ?? $response->body();

            $apiSuccess = $response->successful()
                && $apiSuccessValue !== false
                && ! in_array($apiStatus, ['false', 'failed', 'fail', 'error', 'gagal'], true);

            if (! $apiSuccess) {
                return response()->json([
                    'success' => false,
                    'message' => $apiMessage ? 'Pesan gagal dikirim: ' . $apiMessage : 'Pesan gagal dikirim.',
                    'status'  => $response->status(),
                    'data'    => $responseData,
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $apiMessage ?: 'Pesan berhasil dikirim.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function normalizePhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/\D+/', '', (string) $phoneNumber);

        if (str_starts_with($phoneNumber, '0')) {
            return '62' . substr($phoneNumber, 1);
        }

        if (str_starts_with($phoneNumber, '8')) {
            return '62' . $phoneNumber;
        }

        return $phoneNumber;
    }

    private function resolveTextApiUrl($url)
    {
        if (str_contains($url, '{')) {
            return $url;
        }

        if (str_contains($url, '/messages/text')) {
            return $url;
        }

        if (str_contains($url, '/messages/pdf')) {
            return str_replace('/messages/pdf', '/messages/text', $url);
        }

        $parts = parse_url($url);
        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            return rtrim($url, '/') . '/messages/text';
        }

        $path = $parts['path'] ?? '';
        $base = $parts['scheme'] . '://' . $parts['host'];

        if (! empty($parts['port'])) {
            $base .= ':' . $parts['port'];
        }

        if (preg_match('#/clients/([^/]+)/?$#', $path)) {
            return rtrim($base, '/') . '/messages/text';
        }

        if ($path && $path !== '/') {
            return $url;
        }

        return rtrim($base, '/') . '/messages/text';
    }

    private function extractClientIdFromUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if (preg_match('#/clients/([^/]+)/?$#', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
