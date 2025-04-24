<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TmminController extends Controller
{
    public function index()
    {
        $data = "tmmin";
        return view('konverter', compact('data'));
    }


    public function upload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|file|mimes:txt',
        ]);

        // Read the TXT file
        $file = $request->file('file');
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Prepare Spreadsheet for ADM format
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = [
            "partner_id",
            "partner_invoice_id",
            "partner_shipping_id",
            "pricelist_id",
            "x_studio_po_number",
            "warehouse_id",
            "order_line/product_id/default_code",
            "order_line/product_uom_qty",
            "Analytic Account",
            "tag_ids",
            "client_order_ref",
            "user_id",
            "commitment_date",
        ];

        $letters = range('A', 'M'); // A to M

        // Set headers
        foreach ($columns as $key => $col) {
            $sheet->setCellValue($letters[$key] . '1', $col);
        }

        $prevRef = '';
        $rowOutput = 2;

        foreach ($lines as $line) {
            $fields = explode("\t", $line);

            if (count($fields) < 25) continue;

            $custRef = trim($fields[0]);
            $pracelist = $request->pracelist;
            $partNo = trim($fields[10]);
            $poNumber = trim($fields[0]);
            $deliveryDateTime = trim($fields[24]);
            $deliveryDateTimeFormatted = substr($deliveryDateTime, 0, 19); // Removes .000
            $orderQty = (float) trim($fields[16]);
            if ($orderQty == 0) continue;

            $vendorAlias = "PT Toyota Motor Manufacturing Indonesia";
            $warehouse = "Store Finish Goods";
            $salesperson = $request->salesperson;

            $analyticAccount = "Masspro";
            $tags = "Automotive,Regular";

            if ($custRef !== $prevRef) {
                $sheet->setCellValueExplicit("A$rowOutput", $vendorAlias, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("B$rowOutput", $vendorAlias, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C$rowOutput", $vendorAlias, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("D$rowOutput", $pracelist, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("E$rowOutput", $poNumber, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("F$rowOutput", $warehouse, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("G$rowOutput", $partNo, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("H$rowOutput", $orderQty, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("I$rowOutput", $analyticAccount, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("J$rowOutput", $tags, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("K$rowOutput", $custRef, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("L$rowOutput", $salesperson, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("M$rowOutput", $deliveryDateTimeFormatted, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValueExplicit("G$rowOutput", $partNo, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("H$rowOutput", $orderQty, DataType::TYPE_STRING);
            }

            $prevRef = $custRef;
            $rowOutput++;
        }


        // Save the spreadsheet
        $outputPath = storage_path('app/public/Konverter_TMMIN.xlsx');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}
