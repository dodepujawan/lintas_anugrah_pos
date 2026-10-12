<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Services\PeriodLockService;

class SaldoAwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules()
    {
        $rules = [
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'coa_id' => ['required', 'array', 'min:2'],
            'coa_id.*' => ['nullable', 'integer', Rule::exists('coas', 'id')->where('level', 'Akun Posting')],
            'debit' => ['required', 'array'],
            'debit.*' => ['nullable', 'numeric', 'min:0'],
            'kredit' => ['required', 'array'],
            'kredit.*' => ['nullable', 'numeric', 'min:0'],
            'deskripsi_item' => ['nullable', 'array'],
            'deskripsi_item.*' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->isMethod('post')) {
            $draftId = $this->input('draft_id');
            if ($draftId) {
                $rules['no_jurnal'] = [
                    'required',
                    'string',
                    'max:30',
                    Rule::unique('jurnals', 'no_jurnal')->ignore($draftId),
                ];
            } else {
                $rules['no_jurnal'] = ['required', 'string', 'max:30', 'unique:jurnals,no_jurnal'];
            }
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $ignoreId = $this->input('draft_id')
                ?? $this->route('saldo_awal')?->id
                ?? $this->route('jurnal_penyesuaian')?->id;

            $rules['no_jurnal'] = [
                'required',
                'string',
                'max:30',
                Rule::unique('jurnals', 'no_jurnal')->ignore($ignoreId),
            ];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (app(PeriodLockService::class)->isLocked($this->input('tanggal'))) {
                $validator->errors()->add('tanggal', app(PeriodLockService::class)->lockedMessage($this->input('tanggal')));
            }

            $coaIds = $this->input('coa_id', []);
            $debits = $this->input('debit', []);
            $kredits = $this->input('kredit', []);

            $validRows = 0;
            $totalDebit = 0;
            $totalKredit = 0;

            foreach ($coaIds as $index => $coaId) {
                $debit = isset($debits[$index]) ? (float) $debits[$index] : 0;
                $kredit = isset($kredits[$index]) ? (float) $kredits[$index] : 0;
                $hasCoa = !empty($coaId);
                $hasValues = $debit > 0 || $kredit > 0;

                if (!$hasCoa && !$hasValues) {
                    continue;
                }

                if (!$hasCoa) {
                    $validator->errors()->add('coa_id.' . $index, 'Pilih akun posting untuk baris ini.');
                }

                if ($debit > 0 && $kredit > 0) {
                    $validator->errors()->add('debit.' . $index, 'Debit dan Kredit tidak boleh diisi bersamaan.');
                }

                if ($debit === 0 && $kredit === 0) {
                    $validator->errors()->add('debit.' . $index, 'Masukkan nilai Debit atau Kredit pada baris ini.');
                }

                if ($hasCoa && $hasValues) {
                    $validRows++;
                }

                $totalDebit += $debit;
                $totalKredit += $kredit;
            }

            if ($validRows < 2) {
                $validator->errors()->add('coa_id', 'Minimal dua baris yang valid harus diisi.');
            }

            if (abs($totalDebit - $totalKredit) > 0.01) {
                $validator->errors()->add('debit', 'Total Debit dan Kredit harus sama.');
            }
        });
    }

    public function messages()
    {
        return [
            'coa_id.required' => 'Minimal dua baris harus diisi.',
            'coa_id.array' => 'Format baris tidak valid.',
            'coa_id.min' => 'Minimal dua baris harus diisi.',
            'debit.*.numeric' => 'Nilai Debit harus berupa angka.',
            'kredit.*.numeric' => 'Nilai Kredit harus berupa angka.',
        ];
    }
}
