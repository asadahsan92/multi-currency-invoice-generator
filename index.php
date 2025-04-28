<!-- index.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<!-- Logout Button -->
<div class="flex justify-end max-w-3xl mx-auto mt-4">
    <a href="logout.php"
       class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-all text-sm">
        Logout
    </a>
</div>
<div class="max-w-3xl mx-auto mt-10 bg-white p-8 shadow-lg rounded-xl">
    <h1 class="text-2xl font-bold mb-6">🧾 Generate Invoice</h1>
    <form method="POST" action="invoice.php" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block font-semibold">From</label>
            <input type="text" name="from" required class="w-full border p-2 rounded">
        </div>
        <div class="mb-4">
            <label class="block font-semibold">To</label>
            <input type="text" name="to" required class="w-full border p-2 rounded">
        </div>
        <div class="mb-4">
            <label class="block font-semibold">Currency</label>
            <select name="currency" class="w-full border p-2 rounded">
                <option value="PKR">PKR - Pakistani Rupee</option>
                <option value="USD">USD - US Dollar</option>
                <option value="AED">AED - Dirham</option>
            </select>
        </div>
        <label>Logo (optional): <input type="file" name="logo" accept="image/*"></label><br><br>
        <div id="items" class="space-y-2">
            <div class="item grid grid-cols-3 gap-2">
                <input type="text" name="items[0][name]" placeholder="Item Name" required class="border p-2 rounded">
                <input type="number" name="items[0][qty]" placeholder="Qty" required class="border p-2 rounded">
                <input type="number" name="items[0][rate]" placeholder="Rate" required class="border p-2 rounded">
            </div>
        </div>

        <button type="button" onclick="addItem()" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded">+ Add Item</button>

        <div class="mb-4">
            <label class="block font-semibold">Notes (optional)</label>
            <textarea name="notes" rows="3" placeholder="e.g. Thank you for your business!" class="w-full border p-2 rounded"></textarea>
        </div>

        <div class="mt-6">
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded">Generate PDF</button>
        </div>
    </form>

    <h3 class="text-xl font-semibold mt-8 mb-4">Invoice History</h3>
    <ul class="space-y-2">
        <?php
        if (file_exists('invoices/history.json')) {
            $history = json_decode(file_get_contents('invoices/history.json'), true);
            foreach (array_reverse($history) as $entry) {
                echo '<li class="bg-white p-3 rounded shadow">';
                echo '<span class="font-medium">' . $entry['date'] . '</span> - ' .
                    htmlspecialchars($entry['from']) . ' to ' . htmlspecialchars($entry['to']) . ' — ' .
                    $entry['currency'] . ' ' . $entry['amount'] .
                    ' [<a href="invoices/' . $entry['filename'] . '" target="_blank" class="text-blue-600 underline">Download</a>]';

                if (!empty($entry['notes'])) {
                    echo '<div class="text-sm text-gray-600 mt-1">📝 ' . htmlspecialchars($entry['notes']) . '</div>';
                }

                echo '</li>';
            }
        }
        ?>
    </ul>

</div>

<script>
    let itemCount = 1;
    function addItem() {
        const itemsDiv = document.getElementById('items');
        const div = document.createElement('div');
        div.className = 'item grid grid-cols-3 gap-2';
        div.innerHTML = `
            <input type="text" name="items[${itemCount}][name]" placeholder="Item Name" required class="border p-2 rounded">
            <input type="number" name="items[${itemCount}][qty]" placeholder="Qty" required class="border p-2 rounded">
            <input type="number" name="items[${itemCount}][rate]" placeholder="Rate" required class="border p-2 rounded">
        `;
        itemsDiv.appendChild(div);
        itemCount++;
    }
</script>

</body>
</html>
