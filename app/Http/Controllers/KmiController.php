<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class KmiController extends Controller
{
    public function index()
    {
        $data = "kmi";
        return view('konverter', compact('data'));
    }

    public function upload(Request $request)
    {
        // $request->validate([
        //     'file' => 'required|file|mimes:xlsx,xls',
        //     'salesperson' => 'required|string',
        //     'pricelist' => 'required|string',
        // ]);
        
        try {
            $file = $request->file('file');
            $path = $file->storeAs('private/uploads', 'test2.xlsx');


            $reader = new Xlsx();
            $reader->setReadDataOnly(false);
            $spreadsheet = $reader->load(storage_path('app/private/' . $path));
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();

            $spreadsheetB = new Spreadsheet();
            $sheetB = $spreadsheetB->getActiveSheet();

            $columns = [
                "partner_id", "partner_invoice_id", "partner_shipping_id", "pricelist_id", "x_studio_po_number",
                "warehouse_id", "order_line/product_id/default_code", "x_studio_order_no", "order_line/product_uom_qty",
                "order_line/price_unit", "Analytic Account", "tag_ids", "client_order_ref", "user_id", "commitment_date",
            ];
            $letters = range('A', 'O');


            foreach ($columns as $key => $col) {
                $sheetB->setCellValue($letters[$key] . '1', $col);
            }

            $Salper    = $request->salesperson;
            $Pricelist = $request->pricelist;

            $clientOrderRefAQ = $sheet->getCell("AQ2")->getValue();
            if (substr($clientOrderRefAQ, -1) !== '/') {
                $clientOrderRefAQ .= '/';
            }

            $specialPartNos = [
                "34024-01321K", "34028-04011K", "34028-04001K",
                "34028-01151K", "34028-01121K", "34024-01311K", "34024-01211K"
            ];

            $dataRows = [];

            for ($row = 2; $row <= $highestRow; $row++) {
                $partNo     = preg_replace('/\s+/', '', $sheet->getCell("D{$row}")->getValue());
                $orderNo    = $sheet->getCell("A{$row}")->getValue();
                $custref    = $sheet->getCell("AQ{$row}")->getValue();
                $orderQty   = $sheet->getCell("AM{$row}")->getValue();
                $rawDate    = $sheet->getCell("AF{$row}")->getValue();
                $rawTime    = $sheet->getCell("AC{$row}")->getValue();
                $priceUnit  = $sheet->getCell("AV{$row}")->getValue();

                if (empty($partNo) && empty($custref) && empty($orderQty) && empty($rawDate)) {
                    continue;
                }

                $deliveryDate  = '';
                $formattedAF   = '';
                if (!empty($rawDate)) {
                    if (is_numeric($rawDate)) {
                        $dateObj = Date::excelToDateTimeObject($rawDate);
                        $deliveryDate = $dateObj->format('Y-m-d');
                        $formattedAF  = $dateObj->format('Ymd'); // Ubah ke yyyymmdd
                    } else {
                        $dateObj = \DateTime::createFromFormat('d/m/y', $rawDate);
                        if ($dateObj) {
                            $deliveryDate = $dateObj->format('Y-m-d');
                            $formattedAF  = $dateObj->format('Ymd'); // Ubah ke yyyymmdd
                        }
                    }
                }

                $deliveryTime = '';
                if (!empty($rawTime)) {
                    if (is_numeric($rawTime)) {
                        $deliveryTime = Date::excelToDateTimeObject($rawTime)->format('H:i');
                    } else {
                        $timestamp = strtotime($rawTime);
                        if ($timestamp !== false) {
                            $deliveryTime = date('H:i', $timestamp);
                        }
                    }
                }

                $deliveryCombined = trim("{$deliveryDate} {$deliveryTime}");
                if (empty($formattedAF)) {
                    continue;
                }

                $clientOrderRef = $clientOrderRefAQ . $formattedAF;

                $vendorAlias = "PT Kawasaki Motor Indonesia";
                $plantCode   = "Store Finish Goods";
                $Aac         = "Masspro";
                $poNumber    = trim($custref);

                if (in_array($partNo, $specialPartNos)) {
                    $partNo = preg_replace('/(.*)(1K)$/', '$1 1K', $partNo);
                }

                $dataRows[] = [
                    'vendorAlias'      => $vendorAlias,
                    'pricelist'        => $Pricelist,
                    'poNumber'         => $poNumber,
                    'plantCode'        => $plantCode,
                    'partNo'           => $partNo,
                    'orderNo'          => $orderNo,
                    'orderQty'         => $orderQty,
                    'priceUnit'        => $priceUnit,
                    'Aac'              => $Aac,
                    'clientOrderRef'   => $clientOrderRef,
                    'Salper'           => $Salper,
                    'deliveryCombined' => $deliveryCombined,
                ];
            }

            usort($dataRows, function ($a, $b) {
                return strcmp($a['clientOrderRef'], $b['clientOrderRef']);
            });

            $rowOutput = 2;
            $lastClientOrderRef = null;

            foreach ($dataRows as $entry) {
                $currentClientOrderRef = $entry['clientOrderRef'];
                $clientOrderRefToWrite = $currentClientOrderRef === $lastClientOrderRef ? '' : $currentClientOrderRef;
                $lastClientOrderRef = $currentClientOrderRef;

                if (!empty($clientOrderRefToWrite)) {
                    $sheetB->setCellValue("A{$rowOutput}", $entry['vendorAlias']);
                    $sheetB->setCellValue("B{$rowOutput}", $entry['vendorAlias']);
                    $sheetB->setCellValue("C{$rowOutput}", $entry['vendorAlias']);
                    $sheetB->setCellValue("D{$rowOutput}", $entry['pricelist']);
                    $sheetB->setCellValue("F{$rowOutput}", $entry['plantCode']);
                    $sheetB->setCellValue("K{$rowOutput}", $entry['Aac']);
                    $sheetB->setCellValue("L{$rowOutput}", "Motorcycle,Regular");
                    $sheetB->setCellValue("M{$rowOutput}", $clientOrderRefToWrite);
                    $sheetB->setCellValue("N{$rowOutput}", $entry['Salper']);
                    $sheetB->setCellValue("O{$rowOutput}", $entry['deliveryCombined']);
                }

                $sheetB->setCellValue("E{$rowOutput}", $entry['poNumber']);
                $sheetB->setCellValue("G{$rowOutput}", $entry['partNo']);
                $sheetB->setCellValue("H{$rowOutput}", $entry['orderNo']);
                $sheetB->setCellValue("I{$rowOutput}", $entry['orderQty']);
                $sheetB->setCellValue("J{$rowOutput}", $entry['priceUnit']);

                $rowOutput++;
            }

            // Bagian yang dimodifikasi
            $filename = 'KMI-' . now()->format('d-m-Y_His') . '.xlsx';
            $tempPath = storage_path('app/public/' . $filename);
            $writer = IOFactory::createWriter($spreadsheetB, 'Xlsx');
            $writer->save($tempPath);

            if (!file_exists($tempPath)) {
                return back()->withErrors(['error' => 'File hasil tidak ditemukan. Pastikan folder storage/app/public dapat ditulis.']);
            }

            try {
                return response()->download($tempPath)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Gagal mengunduh file: ' . $e->getMessage()]);
            }

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage()
            ]);
        }
    }
}
