<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Slab Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .commission-slab-card {
            transition: all 0.3s ease;
        }
        .commission-slab-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Commission Slab Management</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 mb-2">Percentage Commission</h3>
                    <p class="text-blue-600">Fixed percentage of total order amount</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">@php echo $lims_commission_slabs->where('type', 'commission_slab_percentage')->first()->value ?? 'N/A'; @endphp</p>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800 mb-2">Flat Rate Commission</h3>
                    <p class="text-green-600">Fixed amount per order</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">@php echo $lims_commission_slabs->where('type', 'commission_slab_flat')->first()->value ?? 'N/A'; @endphp</p>
                </div>
            </div>
            
            <div class="border-t pt-6">
                <h3 class="text-xl font-semibold mb-4">Commission Slabs</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($lims_commission_slabs as $slab)
                        <div class="commission-slab-card bg-gray-50 p-4 rounded-lg border">
                            <h4 class="font-semibold text-gray-800">{{ $slab->key }}</h4>
                            <p class="text-2xl font-bold text-blue-600 mt-2">{{ $slab->value }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $slab->description ?? 'No description' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add slab functionality
            document.getElementById('add-slab')?.addEventListener('click', function() {
                const container = document.getElementById('slabs-container');
                const newRow = document.createElement('div');
                newRow.className = 'slab-row input-group mb-2';
                newRow.innerHTML = `
                    <input type="text" name="keys[]" class="form-control" placeholder="Key (e.g., commission_slab_1_50)">
                    <input type="text" name="values[]" class="form-control" placeholder="Value (e.g., 5%)">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-slab">-</button>
                    </div>
                `;
                container.appendChild(newRow);
            });

            // Remove slab functionality
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-slab')) {
                    e.target.closest('.slab-row').remove();
                }
            });
        });
    </script>
</body>
</html>