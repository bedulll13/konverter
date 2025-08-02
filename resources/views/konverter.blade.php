<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metindo Konverter</title>
    <link rel="shortcut icon" href="{{asset('logo.png')}}" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white w-full max-w-sm rounded-xl shadow-md p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 text-center">Upload File</h2>
        <form class="space-y-4" 
        @if($data == "tmmin")action="{{ route("tmmin.upload")}}"
        @elseif($data == "adm")action="{{ route("adm.upload")}}"
        @elseif($data == "yimm")action="{{ route("yimm.upload")}}"
        @elseif($data == "hpm")action="{{ route("hpm.upload")}}"
        @elseif($data == "simr2")action="{{ route("simr2.upload")}}"
        @elseif($data == "simr4")action="{{ route("simr4.upload")}}"
        @elseif($data == "kmi")action="{{ route("kmi.upload")}}"
        @elseif($data == "alva")action="{{ route("alva.upload")}}"
        @elseif($data == "hino")action="{{ route("hino.upload")}}"
        @endif
        method="POST" enctype="multipart/form-data">
            @csrf
            @if(in_array($data, ['yimm', 'adm', 'tmmin', 'hino']))
            <div class="space-y-1">
                <label for="" class="block text-sm font-medium text-gray-700">Choose Partner Shipping</label>
                <select name="partship" id="" class="w-full rounded-md border border-gray-300 p-2 text-sm">
                    <option value="">-- SELECT AN OPTION --</option>
                    <option value="PT Toyota Motor Manufacturing Indonesia, Sunter">PT Toyota Motor Manufacturing Indonesia, Sunter</option>
                    <option value="PT Toyota Motor Manufacturing Indonesia, Karawang">PT Toyota Motor Manufacturing Indonesia, Karawang</option>
                    <option value="PT Astra Daihatsu Motor, SAP">PT Astra Daihatsu Motor, SAP</option>
                    <option value="PT Astra Daihatsu Motor, KAP">PT Astra Daihatsu Motor, KAP</option>
                    <option value="PT Astra Daihatsu Motor, SPD">PT Astra Daihatsu Motor, SPD</option>
                    <option value="PT Yamaha Indonesia Motor Manufacturing, PG">PT Yamaha Indonesia Motor Manufacturing, PG</option>
                    <option value="PT Yamaha Indonesia Motor Manufacturing, WJ">PT Yamaha Indonesia Motor Manufacturing, WJ</option>
                    <option value="PT Yamaha Indonesia Motor Manufacturing, POD">PT Yamaha Indonesia Motor Manufacturing, POD</option>
                    <option value="PT Hino Motor Sales Indonesia, Purwakarta">PT Hino Motor Sales Indonesia, Purwakarta</option>
                    <option value="PT Hino Motor Sales Indonesia, Tangerang">PT Hino Motor Sales Indonesia, Tangerang</option>
                </select>
            </div>
            @endif
            <div class="space-y-1">
                <label for="" class="block text-sm font-medium text-gray-700">Choose salesperson</label>
                <select name="salesperson" id="" class="w-full rounded-md border border-gray-300 p-2 text-sm">
                    <option value="">-- SELECT AN OPTION --</option>
                    <option value="PUTRI NURFADILLAH">PUTRI NURFADILLAH</option>
                    <option value="FEBRIA ANDRIYANI">FEBRIA ANDRIYANI</option>
                </select>
            </div>
            @if($data == 'yimm')
            <div class="space-y-1">
            <label for="" class="block text-sm font-medium text-gray-700">Choose tags</label>
            <select name="tags" id="" class="w-full rounded-md border border-gray-300 p-2 text-sm">
                    <option value="">-- SELECT AN OPTION --</option>
                    <option value="Motorcycle,Regular">Motorcycle,Regular</option>
                    <option value="Motorcycle,Sparepart">Motorcycle,Sparepart</option>
                </select>
            </div>
            @endif
            <div class="space-y-1">
                <label for="" class="block text-sm font-medium text-gray-700">Pricelist</label>
                <input type="text" required  class="w-full rounded-md border border-gray-300 p-2 text-sm" name="pracelist">
            </div>
            <div class="space-y-1">
                <label for="file" class="block text-sm font-medium text-gray-700">Choose file</label>
                <input type="file" id="file" name="file" class="w-full rounded-md border border-gray-300 p-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer" />
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="window.location.href='/'" class="text-sm px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="text-sm px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                    Download
                </button>
            </div>
        </form>
    </div>

</body>

</html>
