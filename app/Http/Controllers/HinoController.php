<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;

class HinoController extends Controller
{
    public function index()
    {
        $data = "hino";
        return view('konverter', compact('data'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf',
        ]);

        // Data statis yang akan muncul pada baris pertama
        $salesperson = $request->salesperson;
        $pricelist = $request->pricelist;
        $partner = $request->partner;
        $partner_id = "PT Hino Motors Manufacturing Indonesia";
        $warehouse = "Store Finish Goods";
        $analityc = "Masspro";
        $tags = "Automotive,Regular";
        $pdfPath = $request->file('file')->store('pdfs', 'public');
        $fullPdfPath = storage_path("app/public/{$pdfPath}");

        // Parsing file PDF
        $parser = new Parser();
        $pdf = $parser->parseFile($fullPdfPath);
        $ocrText = $pdf->getText();

        // Mengambil data nomor dokumen
        preg_match('/NO\s*:\s*(\d+)/i', $ocrText, $noMatch);
        $documentNo = $noMatch[1] ?? 'UNKNOWN';

        // Mengambil tanggal OP dari teks PDF
        // Mengambil tanggal dari DEL. DATE
        preg_match('/DEL\. DATE\s*:\s*(\d{2}\/\d{2}\/\d{4})/i', $ocrText, $dateMatch);
        $rawDate = $dateMatch[1] ?? 'UNKNOWN_DATE';

        // Mengambil informasi OP DATE
        preg_match('/OP\. DATE\s*:\s*(\d{2}\/\d{2}\/\d{4})\s*-\s*(\d{2}:\d{2})/i', $ocrText, $opDateMatch);

        $rawTime = $opDateMatch[2] ?? 'UNKNOWN_TIME';    // Waktu dari OP DATE

        // Gabungkan tanggal dari DEL. DATE dan waktu dari OP DATE
        $opDate = $rawDate . ' ' . $rawTime;


        // Menyaring setiap baris untuk mendapatkan data produk
        $lines = explode("\n", $ocrText);
        $loopedData = [];

        foreach ($lines as $line) {
            if (preg_match('/^\d+\s+[A-Z0-9\-]+\s+.+\d{5,}/i', $line)) {
                $segments = preg_split('/[\s\t]+/', trim($line));
                $total = count($segments);

                if (strlen($segments[2]) > 9) {
                    $partNo1 = substr($segments[2], 0, 9);
                    $partNo2 = substr($segments[2], 9); // ambil part kedua sebagai kode produk
                    $qty3 = $segments[$total - 1];

                    $loopedData[] = [
                        'product_id' => $partNo2,
                        'product_uom_qty' => $qty3
                    ];
                } else {
                    $partNo = $segments[3];
                    $qty3 = $segments[$total - 1];

                    $loopedData[] = [
                        'product_id' => $partNo,
                        'product_uom_qty' => $qty3
                    ];
                }
            }
        }
        $productPertama = $loopedData[0] ?? ['product_id' => '', 'product_uom_qty' => ''];

        // Data statis untuk baris pertama
        $staticData = [
            $partner_id,
            $partner_id,
            $partner,
            $pricelist,
            '', // x_studio_po_number
            $warehouse,
            $productPertama['product_id'],     // order_line/product_id/default_code
            $productPertama['product_uom_qty'],
            $analityc,
            $tags,
            $documentNo,
            $salesperson,
            $opDate
        ];

        // Menyiapkan data untuk ditulis ke Excel
        $finalData = [];

        // Baris pertama berisi data statis
        $finalData[] = $staticData;

        // Menambahkan produk pada baris-baris berikutnya (mulai dari baris kedua)
        foreach (array_slice($loopedData, 1) as $item) {
            // Salin ulang isi staticData secara utuh
            $row = array_values($staticData);

            // Kosongkan kolom 0–5
            for ($i = 0; $i < 6; $i++) {
                $row[$i] = '';
            }

            // Kosongkan kolom 8–12
            for ($u = 8; $u < 13; $u++) {
                $row[$u] = '';
            }

            // Isi kolom produk
            $row[6] = $item['product_id'];
            $row[7] = $item['product_uom_qty'];

            // Masukkan ke array final
            $finalData[] = $row;
        }


        // Output ke Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom Excel
        $headers = [
            'partner_id',
            'partner_invoice_id',
            'partner_shipping_id',
            'pricelist_id',
            'x_studio_po_number',
            'warehouse_id',
            'order_line/product_id/default_code',
            'order_line/product_uom_qty',
            'Analytic Account',
            'tag_ids',
            'client_order_ref',
            'user_id',
            'commitment_date'
        ];
        $sheet->fromArray($headers, NULL, 'A1');  // Header pada baris pertama
        $sheet->fromArray($finalData, NULL, 'A2');  // Data mulai dari baris kedua

        // Menyimpan file Excel
        $writer = new Xlsx($spreadsheet);
        $fileName = 'delivery_note_' . time() . '.xlsx';
        $filePath = storage_path("app/public/{$fileName}");
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
