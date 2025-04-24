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

        <form class="space-y-4" action="{{ $data == 'tmmin' ? route('tmmin.upload') : route('excel.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-1">
                <label for="" class="block text-sm font-medium text-gray-700">Choose salesperson</label>
                <select name="salesperson" id="" class="w-full rounded-md border border-gray-300 p-2 text-sm">
                    <option value="">-- SELECT AN OPTION --</option>
                    <option value="PUTRI NURFADILLAH">PUTRI NURFADILLAH</option>
                    <option value="FEBRIA ANDRIYANI">FEBRIA ANDRIYANI</option>
                </select>
            </div>
            <div class="space-y-1">
                <label for="" class="block text-sm font-medium text-gray-700">Pricelist</label>
                <input type="text" required  class="w-full rounded-md border border-gray-300 p-2 text-sm" name="pracelist">
            </div>
            <div class="space-y-1">
                <label for="file" class="block text-sm font-medium text-gray-700">Choose file</label>
                <input type="file" id="file" name="file" class="w-full rounded-md border border-gray-300 p-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer" />
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="text-sm px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-100">
                    Cancel
                </button>
                <button type="submit" class="text-sm px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                    Upload
                </button>
            </div>
        </form>
    </div>

</body>

</html>