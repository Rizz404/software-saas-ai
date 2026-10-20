<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Modules\Catalog\Actions\SaveProduct;
use App\Modules\Catalog\Enums\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    use ResolvesCurrentCompany;

    public function index(Request $request): View
    {
        $company = $this->currentCompany($request);

        $products = $company->products()->orderBy('category')->orderBy('sort_order')->get();

        return view('admin.products.index', compact('company', 'products'));
    }

    public function create(Request $request): View
    {
        $company = $this->currentCompany($request);

        return view('admin.products.form', [
            'company' => $company,
            'product' => new Product(['category' => Product::CATEGORY_WEB, 'is_active' => true]),
            'categories' => ProductCategory::values(),
        ]);
    }

    public function store(ProductRequest $request, SaveProduct $save): RedirectResponse
    {
        $product = $save->handle($this->currentCompany($request), $request->productAttributes());

        return redirect()->route('admin.products.index')->with('status', "Product \"{$product->name}\" created.");
    }

    public function edit(Request $request, Product $product): View
    {
        $company = $this->currentCompany($request);
        Gate::authorize('update', $product);

        return view('admin.products.form', [
            'company' => $company,
            'product' => $product,
            'categories' => ProductCategory::values(),
        ]);
    }

    public function update(ProductRequest $request, Product $product, SaveProduct $save): RedirectResponse
    {
        $save->handle($this->currentCompany($request), $request->productAttributes(), $product);

        return redirect()->route('admin.products.index')->with('status', "Product \"{$product->name}\" updated.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->currentCompany($request);
        Gate::authorize('delete', $product);

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }
}
