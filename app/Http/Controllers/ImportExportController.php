<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ImportExportController extends Controller
{
    
    public function exportProducts(): JsonResponse
    {
        $products = Product::with('category')->get();

        $csvData = "SKU,Name,Description,Category,Cost Price,Selling Price,Stock,Min Stock,Reorder Point,Unit,Barcode,Status\n";
        
        foreach ($products as $product) {
            $csvData .= implode(',', [
                $this->escapeCsv($product->sku),
                $this->escapeCsv($product->name),
                $this->escapeCsv($product->description),
                $this->escapeCsv($product->category?->name ?? ''),
                $product->cost_price,
                $product->selling_price,
                $product->quantity_in_stock,
                $product->minimum_stock_level,
                $product->reorder_point,
                $this->escapeCsv($product->unit),
                $this->escapeCsv($product->barcode),
                $product->is_active ? 'Active' : 'Inactive',
            ]) . "\n";
        }

        return response()->json([
            'message' => 'Products exported successfully',
            'data' => [
                'filename' => 'products_' . date('Y-m-d_His') . '.csv',
                'content' => $csvData,
                'count' => $products->count(),
            ]
        ]);
    }

    public function importProducts(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'csv_data' => 'required|string',
            'skip_duplicates' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $csvData = $request->csv_data;
        $skipDuplicates = $request->boolean('skip_duplicates', true);
        
        $lines = explode("\n", trim($csvData));
        $header = str_getcsv(array_shift($lines));
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $lineNumber => $line) {
            if (empty(trim($line))) continue;
            
            try {
                $data = str_getcsv($line);
                $row = array_combine($header, $data);

                if ($skipDuplicates && Product::where('sku', $row['SKU'])->exists()) {
                    $skipped++;
                    continue;
                }

                $category = null;
                if (!empty($row['Category'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $row['Category']],
                        ['is_active' => true]
                    );
                }

                Product::create([
                    'sku' => $row['SKU'],
                    'name' => $row['Name'],
                    'description' => $row['Description'] ?? null,
                    'category_id' => $category?->id,
                    'cost_price' => $row['Cost Price'] ?? 0,
                    'selling_price' => $row['Selling Price'] ?? 0,
                    'quantity_in_stock' => $row['Stock'] ?? 0,
                    'minimum_stock_level' => $row['Min Stock'] ?? 10,
                    'reorder_point' => $row['Reorder Point'] ?? 20,
                    'unit' => $row['Unit'] ?? 'pcs',
                    'barcode' => $row['Barcode'] ?? null,
                    'is_active' => ($row['Status'] ?? 'Active') === 'Active',
                ]);

                $imported++;

            } catch (\Exception $e) {
                $errors[] = "Line " . ($lineNumber + 2) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => 'Import completed',
            'data' => [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ]
        ]);
    }

    public function exportSuppliers(): JsonResponse
    {
        $suppliers = Supplier::all();

        $csvData = "Name,Company Name,Email,Phone,Address,City,State,Country,Postal Code,Tax ID,Status\n";
        
        foreach ($suppliers as $supplier) {
            $csvData .= implode(',', [
                $this->escapeCsv($supplier->name),
                $this->escapeCsv($supplier->company_name),
                $this->escapeCsv($supplier->email),
                $this->escapeCsv($supplier->phone),
                $this->escapeCsv($supplier->address),
                $this->escapeCsv($supplier->city),
                $this->escapeCsv($supplier->state),
                $this->escapeCsv($supplier->country),
                $this->escapeCsv($supplier->postal_code),
                $this->escapeCsv($supplier->tax_id),
                $supplier->is_active ? 'Active' : 'Inactive',
            ]) . "\n";
        }

        return response()->json([
            'message' => 'Suppliers exported successfully',
            'data' => [
                'filename' => 'suppliers_' . date('Y-m-d_His') . '.csv',
                'content' => $csvData,
                'count' => $suppliers->count(),
            ]
        ]);
    }

    public function importSuppliers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'csv_data' => 'required|string',
            'skip_duplicates' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $csvData = $request->csv_data;
        $skipDuplicates = $request->boolean('skip_duplicates', true);
        
        $lines = explode("\n", trim($csvData));
        $header = str_getcsv(array_shift($lines));
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $lineNumber => $line) {
            if (empty(trim($line))) continue;
            
            try {
                $data = str_getcsv($line);
                $row = array_combine($header, $data);

                if ($skipDuplicates && !empty($row['Email']) && Supplier::where('email', $row['Email'])->exists()) {
                    $skipped++;
                    continue;
                }

                Supplier::create([
                    'name' => $row['Name'],
                    'company_name' => $row['Company Name'] ?? null,
                    'email' => $row['Email'] ?? null,
                    'phone' => $row['Phone'] ?? null,
                    'address' => $row['Address'] ?? null,
                    'city' => $row['City'] ?? null,
                    'state' => $row['State'] ?? null,
                    'country' => $row['Country'] ?? null,
                    'postal_code' => $row['Postal Code'] ?? null,
                    'tax_id' => $row['Tax ID'] ?? null,
                    'is_active' => ($row['Status'] ?? 'Active') === 'Active',
                ]);

                $imported++;

            } catch (\Exception $e) {
                $errors[] = "Line " . ($lineNumber + 2) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => 'Import completed',
            'data' => [
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ]
        ]);
    }

    public function productTemplate(): JsonResponse
    {
        $template = "SKU,Name,Description,Category,Cost Price,Selling Price,Stock,Min Stock,Reorder Point,Unit,Barcode,Status\n";
        $template .= "PROD-001,Sample Product,Sample Description,Electronics,100.00,150.00,50,10,20,pcs,123456789,Active\n";

        return response()->json([
            'message' => 'Product import template',
            'data' => [
                'filename' => 'product_import_template.csv',
                'content' => $template,
            ]
        ]);
    }

    public function supplierTemplate(): JsonResponse
    {
        $template = "Name,Company Name,Email,Phone,Address,City,State,Country,Postal Code,Tax ID,Status\n";
        $template .= "John Doe,Doe Supplies Inc,john@example.com,+1234567890,123 Main St,New York,NY,USA,10001,TAX123456,Active\n";

        return response()->json([
            'message' => 'Supplier import template',
            'data' => [
                'filename' => 'supplier_import_template.csv',
                'content' => $template,
            ]
        ]);
    }

    private function escapeCsv(?string $value): string
    {
        if ($value === null) return '';

        if (strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        
        return $value;
    }
}
