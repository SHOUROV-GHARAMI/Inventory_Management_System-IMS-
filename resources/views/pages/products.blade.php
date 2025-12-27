@extends('layouts.dashboard')

@section('title', 'Products')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-box mr-3"></i>Products
        </h1>
        <p class="text-gray-600 mt-1">Manage your product inventory</p>
    </div>
    
    @if(session('user_role') !== 'Staff')
    <button onclick="showAddProductModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
        <i class="fas fa-plus mr-2"></i>Add Product
    </button>
    @endif
</div>

<!-- Search and Filter -->
<div class="bg-white rounded-xl shadow-lg p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" id="searchInput" placeholder="Search products..." 
               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        
        <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Categories</option>
        </select>
        
        <select id="stockFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">All Stock Levels</option>
            <option value="in_stock">In Stock</option>
            <option value="low_stock">Low Stock</option>
            <option value="out_of_stock">Out of Stock</option>
        </select>
        
        <button onclick="loadProducts()" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition">
            <i class="fas fa-search mr-2"></i>Search
        </button>
    </div>
</div>

<!-- Products Table -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-spinner fa-spin text-3xl mb-3"></i>
                        <p>Loading products...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div id="pagination" class="bg-gray-50 px-6 py-4 border-t border-gray-200"></div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeProductModal()">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800">
                <i class="fas fa-plus-circle mr-2"></i>Add Product
            </h2>
            <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="productForm" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">SKU <span class="text-red-500">*</span></label>
                    <input type="text" id="sku" name="sku" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category_id" name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Category</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                    <input type="text" id="unit" name="unit" placeholder="e.g., pcs, kg, liters" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price <span class="text-red-500">*</span></label>
                    <input type="number" id="cost_price" name="cost_price" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selling Price <span class="text-red-500">*</span></label>
                    <input type="number" id="selling_price" name="selling_price" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity in Stock</label>
                    <input type="number" id="quantity_in_stock" name="quantity_in_stock" min="0" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Stock Level</label>
                    <input type="number" id="minimum_stock_level" name="minimum_stock_level" min="0" value="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Point</label>
                    <input type="number" id="reorder_point" name="reorder_point" min="0" value="20" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                <input type="text" id="barcode" name="barcode" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div class="flex items-center space-x-6">
                <label class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" checked class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
                
                <label class="flex items-center">
                    <input type="checkbox" id="track_inventory" name="track_inventory" checked class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Track Inventory</span>
                </label>
            </div>
            
            <div class="sticky bottom-0 bg-white pt-4 border-t border-gray-200 flex justify-end space-x-4">
                <button type="button" onclick="closeProductModal()" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button type="submit" id="saveProductBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-save mr-2"></i>Save Product
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const authToken = localStorage.getItem('auth_token');

if (!authToken) {
    alert('Session expired. Please login again.');
    window.location.href = '/login';
}

async function loadProducts(page = 1) {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const stock = document.getElementById('stockFilter').value;
    
    let url = `/api/products?page=${page}`;
    if (search) url += `&search=${search}`;
    if (category) url += `&category_id=${category}`;
    if (stock) url += `&stock_status=${stock}`;
    
    try {
        const response = await fetch(url, {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            renderProductsTable(data.data);
            renderPagination(data.meta);
        } else if (response.status === 401) {
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Error loading products:', error);
        document.getElementById('productsTableBody').innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-4 text-center text-red-500">
                    <i class="fas fa-exclamation-circle mr-2"></i>Failed to load products
                </td>
            </tr>
        `;
    }
}

function renderProductsTable(products) {
    const tbody = document.getElementById('productsTableBody');
    
    if (products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-box-open text-3xl mb-3"></i>
                    <p>No products found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = products.map(product => {
        const stockStatus = product.quantity_in_stock <= product.minimum_stock_level 
            ? '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Low Stock</span>'
            : '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">In Stock</span>';
            
        return `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <div class="h-12 w-12 bg-gray-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-gray-400"></i>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">${product.name}</div>
                    <div class="text-sm text-gray-500">${product.description || ''}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">${product.sku}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${product.category?.name || 'N/A'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${product.quantity_in_stock}</td>
                <td class="px-6 py-4 text-sm text-gray-900">$${parseFloat(product.selling_price).toFixed(2)}</td>
                <td class="px-6 py-4">${stockStatus}</td>
                <td class="px-6 py-4 text-sm">
                    <button onclick="viewProduct(${product.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${userCanEdit() ? `
                        <button onclick="editProduct(${product.id})" class="text-green-600 hover:text-green-900 mr-3">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteProduct(${product.id})" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(meta) {

    document.getElementById('pagination').innerHTML = `
        <div class="flex justify-between items-center">
            <p class="text-sm text-gray-700">
                Showing <span class="font-medium">${meta.from || 0}</span> to <span class="font-medium">${meta.to || 0}</span> of <span class="font-medium">${meta.total}</span> results
            </p>
            <div class="flex space-x-2">
                ${meta.current_page > 1 ? `
                    <button onclick="loadProducts(${meta.current_page - 1})" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Previous</button>
                ` : ''}
                ${meta.current_page < meta.last_page ? `
                    <button onclick="loadProducts(${meta.current_page + 1})" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Next</button>
                ` : ''}
            </div>
        </div>
    `;
}

function userCanEdit() {
    const role = '{{ session("user_role") }}';
    return role !== 'Staff';
}

function viewProduct(id) {
    alert('View product: ' + id);
}

function editProduct(id) {
    alert('Edit product: ' + id);
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        alert('Delete product: ' + id);
    }
}

function showAddProductModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Add Product';
    document.getElementById('productForm').reset();
    document.getElementById('productModal').classList.remove('hidden');
    loadCategoriesForModal();
}

function closeProductModal() {
    document.getElementById('productModal').classList.add('hidden');
}

document.getElementById('productForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const saveBtn = document.getElementById('saveProductBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    const formData = {
        sku: document.getElementById('sku').value,
        name: document.getElementById('name').value,
        description: document.getElementById('description').value,
        category_id: document.getElementById('category_id').value || null,
        cost_price: parseFloat(document.getElementById('cost_price').value),
        selling_price: parseFloat(document.getElementById('selling_price').value),
        quantity_in_stock: parseInt(document.getElementById('quantity_in_stock').value) || 0,
        minimum_stock_level: parseInt(document.getElementById('minimum_stock_level').value) || 0,
        reorder_point: parseInt(document.getElementById('reorder_point').value) || 0,
        unit: document.getElementById('unit').value,
        barcode: document.getElementById('barcode').value,
        is_active: document.getElementById('is_active').checked,
        track_inventory: document.getElementById('track_inventory').checked
    };
    
    try {
        const response = await fetch('/api/products', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (response.ok) {
            alert('Product created successfully!');
            closeProductModal();
            loadProducts();
        } else if (response.status === 403) {
            alert('Error: You do not have permission to create products');
        } else if (response.status === 422) {
            const errors = Object.values(data.errors).flat().join('\n');
            alert('Validation errors:\n' + errors);
        } else {
            alert('Error: ' + (data.message || 'Failed to create product'));
        }
    } catch (error) {
        console.error('Error saving product:', error);
        alert('Failed to save product. Please try again.');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Product';
    }
});

loadProducts();

async function loadCategories() {
    try {
        const response = await fetch('/api/categories', {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const select = document.getElementById('categoryFilter');
            data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadCategoriesForModal() {
    try {
        const response = await fetch('/api/categories', {
            headers: {
                'Authorization': `Bearer ${authToken}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const select = document.getElementById('category_id');
            select.innerHTML = '<option value="">Select Category</option>';
            data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

loadCategories();
</script>
@endpush
@endsection
