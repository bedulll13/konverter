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
        $request->validate([
            'file' => 'required|file|mimes:txt',
        ]);

        $file = $request->file('file');
        $lines = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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
            "x_studio_order_number",
            "order_line/product_uom_qty",
            "Analytic Account",
            "tag_ids",
            "client_order_ref",
            "user_id",
            "commitment_date",
        ];

        $letters = range('A', 'N');
        foreach ($columns as $key => $col) {
            $sheet->setCellValue($letters[$key] . '1', $col);
        }

        $customFormattedParts = [
            '53217BZ03000' => '53217-BZ030-00',
            '53202BZ14000' => '53202-BZ140-00',
            '53203BZ24000' => '53203-BZ240-00',
            '53205BZ29100' => '53205-BZ291-00',
            '53209BZ16000' => '53209-BZ160-00',
            '53213BZ13000' => '53213-BZ130-00',
            '53214BZ11000' => '53214-BZ110-00',
            '53215BZ26000' => '53215-BZ260-00',
            '57841BZ09000' => '57841-BZ090-00',
            '57842BZ01000' => '57842-BZ010-00',
            '63134BZ28000' => '63134-BZ280-00',
            '63138BZ05000' => '63138-BZ050-00',
            '63142BZ17000' => '63142-BZ170-00',
            '63143BZ15000' => '63143-BZ150-00',
            '63144BZ12000' => '63144-BZ120-00',
            '63145BZ06000' => '63145-BZ060-00',
            '67121BZ19000' => '67121-BZ190-00',
            '67122BZ18000' => '67122-BZ180-00',
            '67301BZ21000' => '67301-BZ210-00',
            '67301BZ22000' => '67301-BZ220-00',
            '67302BZ19000' => '67302-BZ190-00',
            '67303BZ20000' => '67303-BZ200-00',
            '67303BZ21000' => '67303-BZ210-00',
            '67304BZ20000' => '67304-BZ200-00',
            '67304BZ21000' => '67304-BZ210-00',
            '67317BZ15000' => '67317-BZ150-00',
            '67318BZ11000' => '67318-BZ110-00',
            '67331BZ13000' => '67331-BZ130-00',
            '67332BZ12000' => '67332-BZ120-00',
            '67335BZ20000' => '67335-BZ200-00',
            '67336BZ19000' => '67336-BZ190-00',
            '67337BZ10000' => '67337-BZ100-00',
            '67338BZ10000' => '67338-BZ100-00',
            '67349BZ13000' => '67349-BZ130-00',
            '67359BZ13000' => '67359-BZ130-00',
            '67443BZ17000' => '67443-BZ170-00',
            '67444BZ15000' => '67444-BZ150-00',
            '67445BZ12000' => '67445-BZ120-00',
            '67446BZ12000' => '67446-BZ120-00',
            '53205BZ29000' => '53205-BZ290-00',
            // '52021KK01000' => '52021KK010',
            // '67349BZ16000' => '67349BZ160',
            // '557010K06000' => '557010K060',
            // '487100K07000' => '487100K070',
            // '487100K15000' => '487100K150',
            // '487100K16000' => '487100K160',
            // '51420KK01000' => '51420KK010',
            // '523460K05000' => '523460K050',
            // '487100K08000' => '487100K080',
            // '55116KK01000' => '55116KK010',
            // '487400K05000' => '487400K050',
            // '487400K09000' => '487400K090',
        ];

        $groupedData = [];

        foreach ($lines as $line) {
            $fields = explode("\t", $line);
            if (count($fields) < 25) continue;

            $custRef = trim($fields[0]);
            $pracelist = $request->pracelist;
            $rawPartNo = trim($fields[10]);
            $partNo = $customFormattedParts[$rawPartNo] ?? $rawPartNo;
            $orderno = trim($fields[6]);
            $deliveryDateTime = trim($fields[24]);
            $deliveryDateTimeFormatted = substr($deliveryDateTime, 0, 19);
            $orderQty = (float) trim($fields[16]);
            if ($orderQty == 0) continue;

            $key = $custRef . '|' . $deliveryDateTimeFormatted . '|' . $partNo;

            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'custRef' => $custRef,
                    'partNo' => $partNo,
                    'orderno' => $orderno,
                    'qty' => $orderQty,
                    'deliveryDateTime' => $deliveryDateTimeFormatted,
                ];
            } else {
                $groupedData[$key]['qty'] += $orderQty;
            }
        }

        $prevRef = '';
        $rowOutput = 2;

        foreach ($groupedData as $item) {
            $custRef = $item['custRef'];
            $partNo = $item['partNo'];
            $orderno = $item['orderno'];
            $orderQty = $item['qty'];
            $deliveryDateTimeFormatted = $item['deliveryDateTime'];

            $vendorAlias = "PT Toyota Motor Manufacturing Indonesia";
            $partship = $request->partship;
            $warehouse = "Store Finish Goods";
            $salesperson = $request->salesperson;
            $analyticAccount = "Masspro";
            $tags = "Automotive,Regular";

            if ($custRef !== $prevRef) {
                $sheet->setCellValueExplicit("A$rowOutput", $vendorAlias, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("B$rowOutput", $vendorAlias, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C$rowOutput", $partship, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("D$rowOutput", $pracelist, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("E$rowOutput", $custRef, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("F$rowOutput", $warehouse, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("G$rowOutput", $partNo, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("H$rowOutput", $orderno, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("I$rowOutput", $orderQty, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("J$rowOutput", $analyticAccount, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("K$rowOutput", $tags, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("L$rowOutput", $custRef, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("M$rowOutput", $salesperson, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("N$rowOutput", $deliveryDateTimeFormatted, DataType::TYPE_STRING);
            } else {
                $sheet->setCellValueExplicit("G$rowOutput", $partNo, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("H$rowOutput", $orderno, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("I$rowOutput", $orderQty, DataType::TYPE_STRING);
            }

            $prevRef = $custRef;
            $rowOutput++;
        }

        $outputPath = storage_path('app/public/Konverter_TMMIN.xlsx');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);

        return response()->download($outputPath)->deleteFileAfterSend(true);
    }
}
