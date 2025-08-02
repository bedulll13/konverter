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
        $data = "adm";
        return view('konverter', compact('data'));
    }
    public function upload(Request $request)
    {
        $file = $request->file('file');
        $path = $file->storeAs('private/uploads', 'test.xlsx');

        $reader = new Xlsx();
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load(storage_path('app/private/' . $path));
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();

        // Prepare new spreadsheet in Format ADM style
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
            "x_studio_order_no",
            "order_line/product_uom_qty",
            "Analytic Account",
            "tag_ids",
            "client_order_ref",
            "user_id",
            "commitment_date",
            "qty_kbn",
            "del_cycle",
        ];

        $letters = range('A', 'P'); // A to P

        // Set headers
        foreach ($columns as $key => $col) {
            $sheetB->setCellValue($letters[$key] . '1', $col);
        }

        $prevRef = '';
        $rowOutput = 2;

        for ($row = 5; $row <= $highestRow; $row++) {
            $vendorAlias = "PT Astra Daihatsu Motor";
            $partship = $request->partship;
            $Pracelist = $request->pracelist;
            $poNumber = $sheet->getCell("L$row")->getValue();
            $plantCode = "Store Finish Goods";
            $partNo = $sheet->getCell("X$row")->getValue();
            $orderno = $sheet->getCell("Z$row")->getValue();
            $custref = $sheet->getCell("K$row")->getValue();
            $orderQty = $sheet->getCell("AD$row")->getValue();
            $qtykbn = $sheet->getCell("AC$row")->getValue();
            $cycle = $sheet->getCell("R$row")->getValue();
            $Aac = "Masspro";
            $Tags = "Automotive,Regular";
            $Salper = $request->salesperson;

            $deliveryCell = $sheet->getCell("P$row");
            $deliveryTime = $sheet->getCell("Q$row")->getValue();
            $rawValue = $deliveryCell->getValue();

            $deliveryDate = Date::excelToDateTimeObject($rawValue)->format('Y-m-d');
            $deliveryTime = Date::excelToDateTimeObject($deliveryTime)->format('H:i');
            $deliveryCombined = $deliveryDate . " " . $deliveryTime;

            $clientRef = $custref;

            if ($clientRef !== $prevRef) {
                $sheetB->setCellValue("A$rowOutput", $vendorAlias);
                $sheetB->setCellValue("B$rowOutput", $vendorAlias);
                $sheetB->setCellValue("C$rowOutput", $partship);
                $sheetB->setCellValue("D$rowOutput", $Pracelist);
                $sheetB->setCellValue("E$rowOutput", $poNumber);
                $sheetB->setCellValue("F$rowOutput", $plantCode);
                $sheetB->setCellValue("G$rowOutput", $partNo);
                $sheetB->setCellValue("H$rowOutput", $orderno);
                $sheetB->setCellValue("I$rowOutput", $orderQty);
                $sheetB->setCellValue("J$rowOutput", $Aac);
                $sheetB->setCellValue("K$rowOutput", $Tags);
                $sheetB->setCellValue("L$rowOutput", $custref);
                $sheetB->setCellValue("M$rowOutput", $Salper);
                $sheetB->setCellValue("N$rowOutput", $deliveryCombined);
                $sheetB->setCellValue("O$rowOutput", $qtykbn); // tambahkan ini
                $sheetB->setCellValue("P$rowOutput", $cycle);
            } else {
                $sheetB->setCellValue("G$rowOutput", $partNo);
                $sheetB->setCellValue("H$rowOutput", $orderno);
                $sheetB->setCellValue("I$rowOutput", $orderQty);
                $sheetB->setCellValue("O$rowOutput", $qtykbn);
            }

            $rowOutput++;
            $prevRef = $clientRef;
        }


        // Save the result
        $writer = IOFactory::createWriter($spreadsheetB, 'Xlsx');
        $tempPath = storage_path('app/public/Konverter_ADM.xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
