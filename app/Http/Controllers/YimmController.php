<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class YimmController extends Controller
{
    public function index()
    {
        $data = "yimm";
        return view('konverter', compact('data'));
    }
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $path = $file->storeAs('private/uploads', 'test.xlsx');

        $reader = new Xlsx();
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load(storage_path('app/' . $path));
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();

        // Prepare new spreadsheet in Format YIMM style
        $spreadsheetB = new Spreadsheet();
        $sheetB = $spreadsheetB->getActiveSheet();

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
            $sheetB->setCellValue($letters[$key] . '1', $col);
        }

        $prevRef = '';
        $rowOutput = 2;

        for ($row = 2; $row <= $highestRow; $row++) {
            $vendorAlias = "PT Yamaha Indonesia Motor Manufacturing";
            $Pracelist = $request->pracelist;
            $poNumber = $sheet->getCell("H$row")->getValue();
            $plantCode = "Store Finish Goods";
            $partNo = $sheet->getCell("F$row")->getValue();
            $custref = $sheet->getCell("H$row")->getValue();
            $orderQty = $sheet->getCell("Q$row")->getValue();
            $Aac = "Masspro";
            $Tags = "Automotive,Regular";
            $Salper = $request->salesperson;

            $deliveryCell = $sheet->getCell("N$row");
            $deliveryTime = $sheet->getCell("P$row")->getValue();
            $rawValue = $deliveryCell->getValue();

            $deliveryDate = Date::excelToDateTimeObject($rawValue)->format('Y-m-d');
            $deliveryTime = Date::excelToDateTimeObject($deliveryTime)->format('H:i');
            $deliveryCombined = $deliveryDate . " " . $deliveryTime;

            $clientRef = $custref;

            if ($clientRef !== $prevRef) {
                // First occurrence of a new client_order_ref: print full row
                $sheetB->setCellValue("A$rowOutput", $vendorAlias);         // Customer
                $sheetB->setCellValue("B$rowOutput", $vendorAlias);         // Invoice Address
                $sheetB->setCellValue("C$rowOutput", $vendorAlias);         // Delivery Address
                $sheetB->setCellValue("D$rowOutput", $Pracelist);           // Pricelist
                $sheetB->setCellValue("E$rowOutput", $poNumber);            // PO Number
                $sheetB->setCellValue("F$rowOutput", $plantCode);           // Warehouse
                $sheetB->setCellValue("G$rowOutput", $partNo);              // Product
                $sheetB->setCellValue("H$rowOutput", $orderQty);            // Quantity
                $sheetB->setCellValue("I$rowOutput", $Aac);                 // Analytic Account
                $sheetB->setCellValue("J$rowOutput", $Tags);                // Tags
                $sheetB->setCellValue("K$rowOutput", $custref);             // Customer Reference
                $sheetB->setCellValue("L$rowOutput", $Salper);              // Salesperson
                $sheetB->setCellValue("M$rowOutput", $deliveryCombined);    // Delivery Date
            } else {
                // Same client_order_ref: only write product & quantity
                $sheetB->setCellValue("G$rowOutput", $partNo);              // Product
                $sheetB->setCellValue("H$rowOutput", $orderQty);            // Quantity
            }

            $rowOutput++;
            $prevRef = $clientRef;
        }


        // Save the result
        $writer = IOFactory::createWriter($spreadsheetB, 'Xlsx');
        $tempPath = storage_path('app/public/Konverter_YIMM.xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
