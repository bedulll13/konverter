<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class KonverterController extends Controller
{
    public function index()
    {
        return view('konverter');
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

        // Prepare new spreadsheet in Format ADM style
        $spreadsheetB = new Spreadsheet();
        $sheetB = $spreadsheetB->getActiveSheet();

        $columns = [
            "Customer",
            "Invoice Address",
            "Delivery Address",
            "Pricelist",
            "PO Number",
            "Warehouse",
            "Order Lines/Product/Internal Reference/part no",
            "Order Lines/Quantity",
            "Analytic Account",
            "Tags",
            "Customer Reference",
            "Salesperson",
            "Delivery Date",
        ];

        $letters = range('A', 'M'); // A to M

        // Set headers
        foreach ($columns as $key => $col) {
            $sheetB->setCellValue($letters[$key] . '1', $col);
        }

        // Loop through ADMKAP source file
        $rowOutput = 2;
        for ($row = 5; $row <= $highestRow; $row++) {
            $vendorAlias = "PT.Astra Daihatsu Motor";                   // Customer
            $poNumber = $sheet->getCell("L$row")->getValue();           // PO Number
            $plantCode = "Store Finish Goods";                          // Warehouse
            $partNo = $sheet->getCell("X$row")->getValue();             // Part No
            $custref = $sheet->getCell("K$row")->getValue();            // Cust Ref
            $orderQty = $sheet->getCell("AD$row")->getValue();          // Order Qty
            $Aac = "Maspro";                                            // Order Qty
            $Salper = $request->salesperson;                            // Order Qty
            $deliveryCell = $sheet->getCell("P$row");       // Delivery Date
            $deliveryTime = $sheet->getCell("Q$row")->getValue();
            $rawValue = $deliveryCell->getValue();

            $deliveryDate = Date::excelToDateTimeObject($rawValue)->format('Y-m-d ');
            $deliveryTime = Date::excelToDateTimeObject($deliveryTime)->format('H:i');

            $deliveryCombined = $deliveryDate . " " . $deliveryTime;
            
            $sheetB->setCellValue("A$rowOutput", $vendorAlias);    // Customer
            $sheetB->setCellValue("B$rowOutput", $vendorAlias);    // Invoice Address
            $sheetB->setCellValue("C$rowOutput", $vendorAlias);    // Delivery Address
            $sheetB->setCellValue("E$rowOutput", $poNumber);       // PO Number
            $sheetB->setCellValue("F$rowOutput", $plantCode);      // Warehouse
            $sheetB->setCellValue("G$rowOutput", $partNo);         // Part No
            $sheetB->setCellValue("H$rowOutput", $orderQty);       // Quantity
            $sheetB->setCellValue("M$rowOutput", $deliveryCombined);   // Delivery Date
            $sheetB->setCellValue("K$rowOutput", $custref);        // Customer Reference
            $sheetB->setCellValue("I$rowOutput", $Aac);        // Customer Reference
            $sheetB->setCellValue("L$rowOutput", $Salper);        // Customer Reference

            $rowOutput++;
        }

        // Save the result
        $writer = IOFactory::createWriter($spreadsheetB, 'Xlsx');
        $tempPath = storage_path('app/public/modified_file.xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
