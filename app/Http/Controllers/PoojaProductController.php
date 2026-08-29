<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Category;
use App\Models\ProductTax;
use App\Models\ProductTranslation;
use App\Models\Upload;
use App\Services\ProductTaxService;
use Auth;

use App\Models\AttributeValue;
use Combinations;
use CoreComponentRepository;
use Artisan;
use Cache;
use Str;
use App\Services\PoojaStockService;

class PoojaProductController extends Controller
{
    protected $poojaStockService;

    public function __construct(PoojaStockService $poojaStockService) {

        $this->poojaStockService = $poojaStockService;

        // Staff Permission Check
        $this->middleware(['permission:show_pooja_products'])->only('index');
        $this->middleware(['permission:add_pooja_product'])->only('create');
        $this->middleware(['permission:edit_pooja_product'])->only('edit');
        $this->middleware(['permission:delete_pooja_product'])->only('destroy');
        $this->middleware(['permission:download_pooja_product'])->only('download');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search    = null;
        $products       = Product::orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $sort_search    = $request->search;
            $products       = $products->where('name', 'like', '%' . $sort_search . '%');
        }
        $products = $products->where('digital', 2)->paginate(10);
        return view('backend.product.pooja_products.index', compact('products', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::where('parent_id', 0)
            ->where('digital', 2)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.pooja_products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product                    = new Product;
        $product->name              = $request->name;
        $product->added_by          = $request->added_by;
        $product->user_id           = Auth::user()->id;
        $product->category_id       = $request->category_id;
        $product->digital           = 2;
        $product->photos            = $request->photos;
        $product->thumbnail_img     = $request->thumbnail_img;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags = implode(',', $tags);

        $product->description       = $request->description;
        $product->unit_price        = $request->unit_price;
        $product->purchase_price    = $request->purchase_price;
        $product->discount          = $request->discount;
        $product->discount_type     = $request->discount_type;

        $product->meta_title        = $request->meta_title;
        $product->meta_description  = $request->meta_description;
        $product->meta_img          = $request->meta_img;

        $product->file_name = $request->file;

        $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)) . '-' . rand(10000, 99999);

         // Product variant
         $choice_options = array();
         if (isset($request->choice_no) && $request->choice_no) {
             $str = '';
             $item = array();
             foreach ($request->choice_no as $key => $no) {
                 $str = 'choice_options_' . $no;
                 $item['attribute_id'] = $no;
                 $attribute_data = array();
                 // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                 foreach ($request->$str as $key => $eachValue) {
                     // array_push($data, $eachValue->value);
                     array_push($attribute_data, $eachValue);
                 }
                 unset($request->$str);
 
                 $item['values'] = $attribute_data;
                 array_push($choice_options, $item);
             }
         }
         $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);
         $product->choice_options = $choice_options;
 
         if (isset($request->choice_no) && $request->choice_no) {
             $attributes = json_encode($request->choice_no);
             unset($request->choice_no);
         } else {
             $attributes = json_encode(array());
         }
         $product->attributes = $attributes;
         // end

        if ($product->save()) {
            $request->merge(['product_id' => $product->id]);

            // Product variant
            $this->poojaStockService->store($request->only([
                'colors_active', 'colors', 'choice_no', 'unit_price', 'sku', 'current_stock', 'product_id'
            ]), $product);
            // end
            
            //VAT & Tax
            if ($request->tax_id) {
                (new ProductTaxService)->store($request->only([
                    'tax_id', 'tax', 'tax_type', 'product_id'
                ]));
            }

            ProductTranslation::updateOrCreate(
                $request->only([
                    'lang', 'product_id'
                ]),
                $request->only([
                    'name', 'unit', 'description'
                ])
            );

            flash(translate('Pooja Service has been inserted successfully'))->success();
            return redirect()->route('poojaproducts.index');
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang = $request->lang;
        $product = Product::findOrFail($id);
        $categories = Category::where('parent_id', 0)
            ->where('digital', 2)
            ->with('childrenCategories')
            ->get();
        return view('backend.product.pooja_products.edit', compact('product', 'lang', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product                    = Product::findOrFail($id);
        if ($request->lang == env("DEFAULT_LANGUAGE")) {
            $product->name          = $request->name;
            $product->description   = $request->description;
        }

        $product->user_id           = Auth::user()->id;
        $product->category_id       = $request->category_id;
        $product->digital           = 2;
        $product->photos            = $request->photos;
        $product->thumbnail_img     = $request->thumbnail_img;

        $tags = array();
        if ($request->tags[0] != null) {
            foreach (json_decode($request->tags[0]) as $key => $tag) {
                array_push($tags, $tag->value);
            }
        }
        $product->tags = implode(',', $tags);

        $product->unit_price        = $request->unit_price;
        $product->purchase_price    = $request->purchase_price;
        $product->discount          = $request->discount;
        $product->discount_type     = $request->discount_type;

        $product->meta_title        = $request->meta_title;
        $product->meta_description  = $request->meta_description;
        $product->meta_img          = $request->meta_img;
        $product->slug              = strtolower($request->slug);

        $product->file_name = $request->file;

        // Product variant
        $choice_options = array();
        if (isset($request->choice_no) && $request->choice_no) {
            $str = '';
            $item = array();
            foreach ($request->choice_no as $key => $no) {
                $str = 'choice_options_' . $no;
                $item['attribute_id'] = $no;
                $attribute_data = array();
                // foreach (json_decode($request[$str][0]) as $key => $eachValue) {
                foreach ($request->$str as $key => $eachValue) {
                    // array_push($data, $eachValue->value);
                    array_push($attribute_data, $eachValue);
                }
                unset($request->$str);

                $item['values'] = $attribute_data;
                array_push($choice_options, $item);
            }
        }
        $choice_options = json_encode($choice_options, JSON_UNESCAPED_UNICODE);
        $product->choice_options = $choice_options;

        if (isset($request->choice_no) && $request->choice_no) {
            $attributes = json_encode($request->choice_no);
            unset($request->choice_no);
        } else {
            $attributes = json_encode(array());
        }
        $product->attributes = $attributes;
        // end

        // Delete From Product Stock
        foreach ($product->stocks as $key => $stock) {
            $stock->delete();
        }

        if ($product->save()) {
            $request->merge(['product_id' => $product->id]);

            // Product variant
            $this->poojaStockService->store($request->only([
                'colors_active', 'colors', 'choice_no', 'unit_price', 'sku', 'current_stock', 'product_id'
            ]), $product);
            // end

            //VAT & Tax
            if ($request->tax_id) {
                ProductTax::where('product_id', $product->id)->delete();
                (new ProductTaxService)->store($request->only([
                    'tax_id', 'tax', 'tax_type', 'product_id'
                ]));
            }

            // Product Translations
            ProductTranslation::updateOrCreate(
                $request->only([
                    'lang', 'product_id'
                ]),
                $request->only([
                    'name', 'unit', 'description'
                ])
            );

            flash(translate('Pooja Service has been updated successfully'))->success();
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return back();
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->product_translations()->delete();
        $product->stocks()->delete();

        Product::destroy($id);

        flash(translate('Service has been deleted successfully'))->success();
        return redirect()->route('poojaproducts.index');
    }


    public function download(Request $request)
    {
        $product = Product::findOrFail(decrypt($request->id));
        $downloadable = false;
        foreach (Auth::user()->orders as $key => $order) {
            foreach ($order->orderDetails as $key => $orderDetail) {
                if ($orderDetail->product_id == $product->id && $orderDetail->payment_status == 'paid') {
                    $downloadable = true;
                    break;
                }
            }
        }
        if (Auth::user()->user_type == 'admin' || Auth::user()->id == $product->user_id || $downloadable) {
            $upload = Upload::findOrFail($product->file_name);
            if (env('FILESYSTEM_DRIVER') == "s3") {
                return \Storage::disk('s3')->download($upload->file_name, $upload->file_original_name . "." . $upload->extension);
            } else {
                if (file_exists(base_path('public/' . $upload->file_name))) {
                    return response()->download(base_path('public/' . $upload->file_name));
                }
            }
        } else {
            abort(404);
        }
    }

    public function updateFeatured(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $product->featured = $request->status;
        if ($product->save()) {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            return 1;
        }
        return 0;
    }

    public function add_more_choice_option(Request $request)
    {
        $all_attribute_values = AttributeValue::with('attribute')->where('attribute_id', $request->attribute_id)->get();

        $html = '';

        foreach ($all_attribute_values as $row) {
            $html .= '<option value="' . $row->value . '">' . $row->value . '</option>';
        }

        echo json_encode($html);
    }

    public function sku_combination(Request $request)
    {
        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $unit_price = $request->unit_price;
        $product_name = $request->name;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                foreach ($request[$name] as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = Combinations::makeCombinations($options);
        return view('backend.product.pooja_products.sku_combinations', compact('combinations', 'unit_price', 'colors_active', 'product_name'));
    }

    public function sku_combination_edit(Request $request)
    {
        $product = Product::findOrFail($request->id);

        $options = array();
        if ($request->has('colors_active') && $request->has('colors') && count($request->colors) > 0) {
            $colors_active = 1;
            array_push($options, $request->colors);
        } else {
            $colors_active = 0;
        }

        $product_name = $request->name;
        $unit_price = $request->unit_price;

        if ($request->has('choice_no')) {
            foreach ($request->choice_no as $key => $no) {
                $name = 'choice_options_' . $no;
                $data = array();
                // foreach (json_decode($request[$name][0]) as $key => $item) {
                foreach ($request[$name] as $key => $item) {
                    // array_push($data, $item->value);
                    array_push($data, $item);
                }
                array_push($options, $data);
            }
        }

        $combinations = Combinations::makeCombinations($options);
        return view('backend.product.pooja_products.sku_combinations_edit', compact('combinations', 'unit_price', 'colors_active', 'product_name', 'product'));
    }
}
