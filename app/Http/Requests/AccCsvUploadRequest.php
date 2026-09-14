<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccCsvUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            /*
             * `mimes:csv` alone rejects real bank exports. Laravel validates
             * the type it sniffs from the uploaded temp file, not the name or
             * what the browser claimed, and its `csv` map is only text/csv,
             * application/csv, text/x-comma-separated-values and text/x-csv.
             * A plain two-column statement sniffs as text/plain — there is
             * nothing in the bytes to say otherwise — so a valid .csv failed.
             *
             * `txt` is added to admit text/plain, and the extension is pinned
             * separately so that widening does not quietly start accepting
             * .txt uploads. The check is a closure rather than `regex`, which
             * stringifies the upload object instead of reading its name, and
             * rejects everything; `extensions:csv` only landed in Laravel 11
             * and this is 10.50.
             */
            'csv_doc' => ['required', 'file', 'mimes:csv,txt', function ($attribute, $value, $fail) {
                if ($value instanceof \Illuminate\Http\UploadedFile
                    && strtolower($value->getClientOriginalExtension()) !== 'csv'):
                    $fail('That is not a .csv file — export the statement as CSV and try again.');
                endif;
            }],
            'cto_receipts' => ['nullable', 'array'],
            'cto_receipts.*' => ['file', 'mimes:pdf'],
        ];
    }

    public function messages()
    {
        return [
            'csv_doc.required' => 'Choose the CSV file exported from your bank.',
            'csv_doc.mimes' => 'That file could not be read as a CSV.',
            'cto_receipts.*.mimes' => 'Receipts must be PDF files.',
        ];
    }

}
