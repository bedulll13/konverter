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
        $spreadsheet = $reader->load(storage_path('app/private/' . $path));
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();

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
        ];

        $letters = range('A', 'N');
        foreach ($columns as $key => $col) {
            $sheetB->setCellValue($letters[$key] . '1', $col);
        }

        $groupedRows = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $poNumberRaw = $sheet->getCell("H$row")->getValue();
            $partNo = $sheet->getCell("F$row")->getValue();
            $orderNo = $sheet->getCell("M$row")->getValue();
            $orderQty = $sheet->getCell("Q$row")->getValue();

            if (empty($orderNo)) {
                continue;
            }

            $deliveryCell = $sheet->getCell("N$row")->getValue();
            $deliveryTime = $sheet->getCell("O$row")->getValue();

            $commitmentDate = '';
            $formattedTime = '';
            $poNumberDate = '';
            $poKey = $poNumberRaw;

            if ($deliveryCell) {
                if (is_numeric($deliveryCell)) {
                    $commitmentDate = Date::excelToDateTimeObject($deliveryCell)->format('Y-m-d');
                    $poNumberDate = Date::excelToDateTimeObject($deliveryCell)->format('Ymd');
                } else {
                    try {
                        $commitmentDate = (new DateTime($deliveryCell))->format('Y-m-d');
                        $poNumberDate = (new DateTime($deliveryCell))->format('Ymd');
                    } catch (\Exception $e) {}
                }

                if ($poNumberDate) {
                    $poKey .= '/' . $poNumberDate;
                }

                if ($deliveryTime) {
                    if (is_numeric($deliveryTime)) {
                        $formattedTime = Date::excelToDateTimeObject($deliveryTime)->format('H:i');
                    } else {
                        try {
                            $formattedTime = (new DateTime($deliveryTime))->format('H:i');
                        } catch (\Exception $e) {}
                    }

                    if (!empty($formattedTime)) {
                        $poKey .= '/' . $formattedTime;
                    }
                }
            }

            $groupedRows[$poKey][] = [
                'vendorAlias' => "PT Yamaha Indonesia Motor Manufacturing",
                'partship' => $request->partship,
                'pricelist' => $request->pracelist,
                'poNumber' => $poKey,
                'plantCode' => "Store Finish Goods",
                'partNo' => $partNo,
                'orderNo' => $orderNo,
                'orderQty' => $orderQty,
                'analyticAccount' => "Masspro",
                'tags' => $request->tags,
                'clientRef' => $poNumberRaw,
                'salesperson' => $request->salesperson,
                'commitmentDate' => trim($commitmentDate . ' ' . $formattedTime),
            ];
        }

        ksort($groupedRows);

        $rowOutput = 2;
        foreach ($groupedRows as $group) {
            $isFirstRow = true;
            foreach ($group as $data) {
                // Format product ID langsung di sini
                $formattedPartNo = substr($data['partNo'], 0, 3) . '-' .
                                   substr($data['partNo'], 3, 5) . '-' .
                                   substr($data['partNo'], 8, 2) . '-' .
                                   substr($data['partNo'], 10, 2) . '-' .
                                   substr($data['partNo'], 12, 2);

                if ($isFirstRow) {
                    $sheetB->setCellValue("A$rowOutput", $data['vendorAlias']);
                    $sheetB->setCellValue("B$rowOutput", $data['vendorAlias']);
                    $sheetB->setCellValue("C$rowOutput", $data['partship']);
                    $sheetB->setCellValue("D$rowOutput", $data['pricelist']);
                    $sheetB->setCellValue("E$rowOutput", $data['poNumber']);
                    $sheetB->setCellValue("F$rowOutput", $data['plantCode']);
                    $sheetB->setCellValue("G$rowOutput", $formattedPartNo);
                    $sheetB->setCellValue("H$rowOutput", $data['orderNo']);
                    $sheetB->setCellValue("I$rowOutput", $data['orderQty']);
                    $sheetB->setCellValue("J$rowOutput", $data['analyticAccount']);
                    $sheetB->setCellValue("K$rowOutput", $data['tags']);
                    $sheetB->setCellValue("L$rowOutput", $data['clientRef']);
                    $sheetB->setCellValue("M$rowOutput", $data['salesperson']);
                    $sheetB->setCellValue("N$rowOutput", $data['commitmentDate']);
                    $isFirstRow = false;
                } else {
                    $sheetB->setCellValue("G$rowOutput", $formattedPartNo);
                    $sheetB->setCellValue("H$rowOutput", $data['orderNo']);
                    $sheetB->setCellValue("I$rowOutput", $data['orderQty']);
                }
                $rowOutput++;
            }
        }

        $writer = IOFactory::createWriter($spreadsheetB, 'Xlsx');
        $tempPath = storage_path('app/public/Konverter_YIMM.xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
