<?php

namespace App\Services;

use App\Models\FileM;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Services\EditFile;
use App\Services\EditMapObject;
use Cache;
use DB;

class EditProduct {

    const OBJECT_MERCHANT = 'Merchant';
    const OBJECT_PRODUCT = 'Product';
    const OBJECT_PAGESIZE = 30;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $editFile;

    /**
     * The Guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $editMapObject;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditFile $editFile, EditMapObject $editMapObject) {
        $this->editFile = $editFile;
        $this->editMapObject = $editMapObject;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProduct(User $user, $product_id) {
$data = [];

        $result = $this->checkAccessProduct($user, $product_id);
        if ($result['access'] == true) {
            $data = Cache::remember('products_' . $product_id,100, function ()use ($product_id) {
                        $product = Product::find($product_id);
                        $data = [];
                        if ($product) {
                            $data['product'] = $product;
                            $data['variants'] = $product->productVariants;
                            $data['files'] = FileM::where("type", self::OBJECT_PRODUCT)->where("trigger_id", $product->id)->get();
                        }
                        return $data;
                    });
            if ($result['owner'] == true) {
                $product = $data['product'];
                $product->mine = true;
                $data['product'] = $product;
            }
        } else {
            $data['message'] = "You dont have access";
        }
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProductsMerchant(User $user, $merchant_id, $page) {
        $data = [];
        $result = $this->checkAccessMerchant($user, $merchant_id);
        if ($result['access'] == true) {
            if (false) {
                $data = Cache::remember('products_merchant_' . $merchant_id . "_" . $page,100, function ()use ($merchant_id, $page) {
                            $data = [];
                            $take = self::OBJECT_PAGESIZE;
                            $skip = ($page - 1 ) * ($take);
                            $variants = DB::table('products')->groupBy('products.id')
                                            ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                                            ->where('merchant_product.merchant_id', $merchant_id)
                                            ->where('products.isActive', true)
                                            ->select('products.*')
                                            ->skip($skip)->take($take)->get();
                            $data['products_total'] = DB::table('products')->groupBy('products.id')
                                    ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                                    ->where('merchant_product.merchant_id', $merchant_id)
                                    ->where('products.isActive', true)
                                    ->count();
                            $products = [];
                            foreach ($variants as $value) {
                                if (in_array($value->id, $products)) {
                                    
                                } else {
                                    array_push($products, $value->id);
                                }
                            }
                            $data['products_variants'] = $variants;
                            $data['products_variants'] = DB::table('products')
                                    ->join('product_variant', 'products.id', '=', 'product_variant.product_id')
                                    ->whereIn('products.id', $products)
                                    ->select('product_variant.*', 'products.id as prod_id', 'products.name as prod_name', 'products.description as prod_desc', 'products.availability as prod_avail')
                                    ->get();
                            $data['products_files'] = DB::table('products')
                                    ->leftJoin('files', 'products.id', '=', 'files.trigger_id')
                                    ->whereIn('files.trigger_id', $products)
                                    ->where('files.type', "Product")
                                    ->select('products.*', 'files.*')
                                    ->get();
                            return $data;
                        });
            } else {

                $take = self::OBJECT_PAGESIZE;
                $skip = ($page - 1 ) * ($take);
                $variants = DB::table('products')->groupBy('products.id')
                                ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                                ->where('merchant_product.merchant_id', $merchant_id)
                                ->where('products.isActive', true)
                                ->select('products.*')
                                ->skip($skip)->take($take)->get();
                $data['products_total'] = DB::table('products')->groupBy('products.id')
                        ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                        ->where('merchant_product.merchant_id', $merchant_id)
                        ->where('products.isActive', true)
                        ->count();
                $products = [];
                foreach ($variants as $value) {
                    if (in_array($value->id, $products)) {
                        
                    } else {
                        array_push($products, $value->id);
                    }
                }
                $data['products_variants'] = $variants;
                $data['products_variants'] = DB::table('products')
                        ->join('product_variant', 'products.id', '=', 'product_variant.product_id')
                        ->whereIn('products.id', $products)
                        ->select('product_variant.*', 'products.id as prod_id', 'products.name as prod_name', 'products.description as prod_desc', 'products.availability as prod_avail')
                        ->get();
                $data['products_files'] = DB::table('products')
                        ->leftJoin('files', 'products.id', '=', 'files.trigger_id')
                        ->whereIn('files.trigger_id', $products)
                        ->where('files.type', "Product")
                        ->select('products.*', 'files.*')
                        ->get();
                return $data;
            }
        }

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProductsPrivateMerchant(User $user, $merchant_id, $page) {
        $data = [];
        $merchant = Merchant::find($merchant_id);
        if ($merchant) {
            if ($merchant->user_id == $user->id) {
                $data = [];
                $take = self::OBJECT_PAGESIZE;
                $skip = ($page - 1 ) * ($take);
                $variants = DB::table('products')->groupBy('products.id')
                                ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                                ->where('merchant_product.merchant_id', $merchant_id)
                                ->select('products.*')
                                ->skip($skip)->take($take)->get();
                $data['products_total'] = DB::table('products')->groupBy('products.id')
                        ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                        ->where('merchant_product.merchant_id', $merchant_id)
                        ->count();
                $products = [];
                foreach ($variants as $value) {
                    if (in_array($value->id, $products)) {
                        
                    } else {
                        array_push($products, $value->id);
                    }
                }
                $data['products_variants'] = $variants;
                $data['products_variants'] = DB::table('products')
                        ->leftJoin('product_variant', 'products.id', '=', 'product_variant.product_id')
                        ->whereIn('products.id', $products)
                        ->select('product_variant.*', 'products.id as prod_id', 'products.name as prod_name', 'products.description as prod_desc', 'products.availability as prod_avail')
                        ->get();
                $data['products_files'] = DB::table('products')
                                    ->leftJoin('files', 'products.id', '=', 'files.trigger_id')
                                    ->whereIn('files.trigger_id', $products)
                                    ->where('files.type', "Product")
                                    ->select('products.*', 'files.*')
                                    ->get();
                return $data;
            }
        }
        return null;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProductsGroup(User $user, $group_id, $page) {
$data = [];
        $result = $this->checkAccessGroup($user, $group_id);
        if ($result['access'] == true) {
            if ($page < 4) {
                $data = Cache::remember('products_group_' . $group_id . "_" . $page, 100, function ()use ($group_id, $page) {
                            $data = [];
                            $take = self::OBJECT_PAGESIZE;
                            $skip = ($page - 1 ) * ($take);
                            $variants = DB::table('products')->groupBy('products.id')
                                            ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                                            ->join('mechants', 'mechants.id', '=', 'merchant_product.mechant_id')
                                            ->join('group_merchant', 'mechants.id', '=', 'group_merchant.mechant_id')
                                            ->where('group_merchant.group_id', $group_id)
                                            ->where('merchants.status', 'active')
                                            ->where('products.isActive', true)
                                            ->select('products.*', 'mechants.id as merchant_id')
                                            ->skip($skip)->take($take)->get();
                            $data['products_total'] = DB::table('products')->groupBy('products.id')
                                    ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                                    ->join('mechants', 'mechants.id', '=', 'merchant_product.mechant_id')
                                    ->join('group_merchant', 'mechants.id', '=', 'group_merchant.mechant_id')
                                    ->where('group_merchant.group_id', $group_id)
                                    ->where('merchants.status', 'active')
                                    ->where('products.isActive', true)
                                    ->count();
                            $products = [];
                            foreach ($variants as $value) {
                                if (in_array($value->id, $products)) {
                                    
                                } else {
                                    array_push($products, $value->id);
                                }
                            }
                            $data['products_variants'] = $variants;
                            $data['products_variants'] = DB::table('products')
                                    ->join('product_variant', 'products.id', '=', 'product_variant.product_id')
                                    ->whereIn('products.id', $products)
                                    ->select('product_variant.*', 'products.id as prod_id', 'products.name as prod_name', 'products.description as prod_desc', 'products.availability as prod_avail')
                                    ->get();
                            $data['products_files'] = DB::table('products')
                                    ->leftJoin('files', 'products.id', '=', 'files.trigger_id')
                                    ->whereIn('files.trigger_id', $products)
                                    ->where('files.type', "Product")
                                    ->select('products.*', 'files.*')
                                    ->get();
                            return $data;
                        });
            } else {
                $data = [];
                $take = self::OBJECT_PAGESIZE;
                $skip = ($page - 1 ) * ($take);
                $variants = DB::table('products')->groupBy('products.id')
                                ->join('merchant_product', 'products.id', '=', 'merchant_product.product_id')
                                ->join('mechants', 'mechants.id', '=', 'merchant_product.mechant_id')
                                ->join('group_merchant', 'mechants.id', '=', 'group_merchant.mechant_id')
                                ->where('group_merchant.group_id', $group_id)
                                ->where('merchants.status', 'active')
                                ->where('products.isActive', true)
                                ->select('products.*')
                                ->skip($skip)->take($take)->get();
                $products = [];
                foreach ($variants as $value) {
                    if (in_array($value->id, $products)) {
                        
                    } else {
                        array_push($products, $value->id);
                    }
                }
                $data['products_variants'] = $variants;
                $data['products_variants'] = DB::table('products')
                        ->join('product_variant', 'products.id', '=', 'product_variant.product_id')
                        ->whereIn('products.id', $products)
                        ->select('product_variant.*', 'products.id as prod_id', 'products.name as prod_name', 'products.description as prod_desc', 'products.availability as prod_avail')
                        ->get();
                $data['products_files'] = DB::table('products')
                        ->leftJoin('files', 'products.id', '=', 'files.trigger_id')
                        ->whereIn('files.trigger_id', $products)
                        ->where('files.type', "Product")
                        ->select('products.*', 'files.*')
                        ->get();
                return $data;
            }
        }

        return $data;
    }

    public function getVariant(User $user, $product_id, $variantId) {
$data = [];
        $result = $this->checkAccessProduct($user, $product_id);
        if ($result['access'] == true) {
            $data = Cache::remember('products_' . $product_id . '_variant_' . $variantId, 100, function ()use ( $variantId) {
                        $data = [];
                        $data['variant'] = ProductVariant::find($variantId);
                        return $data;
                    });
        }
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkAccessMerchant(User $user, $merchant_id) {
        $merchant = Merchant::find($merchant_id);
        $data = [];
        $access = false;
        if ($merchant) {
            if ($merchant->is_public) {
                $access = true;
            } else {
                if ($user) {
                    $members = DB::select('select u.user_id as id '
                                    . 'from userables u '
                                    . 'JOIN merchants m ON m.id =u.object_id '
                                    . 'where u.user_id  = ? '
                                    . 'and u.userable_type = "Merchant" '
                                    . 'and m.status = "active" '
                                    . 'and u.object_id = ? ', [$user->id, $merchant->id]);
                    if (sizeof($members) > 0) {
                        $access = true;
                    }
                    $groups = DB::select('SELECT 
                                            DISTINCT(m.id)
                                        FROM
                                            groups g
                                                JOIN
                                            group_user gu ON gu.group_id = g.id
                                                JOIN
                                            group_merchant gm ON gm.group_id = g.id
                                                JOIN
                                            merchants m ON gm.merchant_id = m.id
                                        WHERE
                                            m.status = "active"
                                                AND g.status = "active"
                                                AND gm.status = "active"
                                                AND gu.level = "active"
                                                AND m.status = "active"
                                                AND m.private = false
                                                AND gu.user_id = ?
                                                AND gm.merchant_id = ?;', [$user->id, $merchant->id]);
                    if (sizeof($groups) > 0) {
                        $access = true;
                    }
                    if ($user->id == $merchant->user_id) {
                        $access = true;
                    }
                }
            }
        }
        $data = [
            "access" => $access,
            "owner_id" => $merchant->user_id
        ];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkAccessProduct(User $user, $product_id) {
        $access = false;
        $owner = false;
        $data = [];
        $damerchant = DB::select('SELECT 
                                            DISTINCT(m.id),m.user_id
                                        FROM
                                            merchants m
                                        WHERE
                                                m.status = "active"
                                                AND m.private = false
                                                AND m.id IN ( 
                                                SELECT merchant_id from merchant_product WHERE product_id = ? 
                                                )

                ;', [$product_id]);
        if (sizeof($damerchant) > 0) {
            $access = true;
            if ($damerchant[0]->user_id == $user->id) {
                $owner = true;
            } else {
                $owner = false;
            }
        } else {
            $members = DB::select('select user_id as id '
                            . 'from userables '
                            . 'where user_id  = ? '
                            . 'and userable_type = "Merchant" '
                            . 'and object_id IN ( 
                                                SELECT merchant_id from merchant_product mp 
                                                JOIN merchants m ON m.id = mp.merchant_id WHERE mp.product_id = ? AND m.status = "active"
                                                ) ', [$user->id, $product_id]);
            if (sizeof($members) > 0) {
                $access = true;
            } else {
                $groups = DB::select('SELECT 
                                            DISTINCT(m.id),m.user_id
                                        FROM
                                            groups g
                                                JOIN
                                            group_user gu ON gu.group_id = g.id
                                                JOIN
                                            group_merchant gm ON gm.group_id = g.id
                                                JOIN
                                            merchants m ON gm.merchant_id = m.id
                                        WHERE
                                            m.status = "active"
                                                AND g.status = "active"
                                                AND gm.status = "active"
                                                AND gu.level = "active"
                                                AND m.status = "active"
                                                AND m.private = false
                                                AND gu.user_id = ?
                                                AND gm.merchant_id IN ( 
                                                SELECT merchant_id from merchant_product WHERE product_id = ? 
                                                )

                    ;', [$user->id, $product_id]);
                if (sizeof($groups) > 0) {
                    $access = true;
                } else {
                    
                }
            }
        }
        $data = [
            "access" => $access,
            "owner" => $owner
        ];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkAccessGroup(User $user, $group_id) {
        $access = false;
        $groups = DB::select('SELECT 
                                            DISTINCT(gu.user_id)
                                        FROM
                                            groups g
                                                JOIN
                                            group_user gu ON gu.group_id = g.id
                                        WHERE
                                                g.status = "active"
                                                AND gu.level = "active"
                                                AND gu.user_id = ?
                                                AND g.id = ?

                   ;', [$user->id, $group_id]);
        if (sizeof($groups) > 0) {
            $access = true;
        }
        $data = [
            "access" => $access
        ];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function writeAccessProduct(User $user, $product_id) {
        $access = false;
        $product = Product::find($product_id);
        if ($product) {
            if ($product->user_id == $user->id) {
                $access = true;
            }
        } else {
            $access = true;
        }

        $data = [
            "access" => $access
        ];
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteProduct(User $user, $productId) {
        $product = Product::find($productId);
        if ($product) {
            $write = $this->writeAccessProduct($user, $productId);
            if ($write['access'] == true) {
                $variants = $product->productVariants;
                $product->conditions()->delete();
                $product->merchants()->detach();
                $files = FileM::where("type", self::OBJECT_PRODUCT)->where("trigger_id", $product->id)->get();
                foreach ($files as $file) {
                    $this->editFile->delete($user, $file->id);
                }
                foreach ($variants as $variant) {
                    $variant->conditions()->delete();
                    $variant->items()->delete();
                    $variant->delete();
                }
                $product->clearCache();
                $product->delete();
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function deleteVariant(User $user, $variantId) {
        $variant = ProductVariant::find($variantId);
        if ($variant) {
            $write = $this->writeAccessProduct($user, $variant->product_id);
            if ($write['access'] == true) {
                $variant->conditions()->delete();
                $variant->items()->delete();
                $variant->delete();
            }
        }
    }

    public function createOrUpdateProduct(User $user, array $data) {
        $result = $this->writeAccessProduct($user, $data["id"]);
        $product = null;
        if ($result['access'] == true) {
            if ($data["id"]) {
                $productid = $data['id'];
                unset($data['id']);
                unset($data['merchant_id']);
                $data = (object) array_filter((array) $data, function ($val) {
                            return !is_null($val);
                        });
                $data = (array) $data;
                Product::where('id', $productid)->update($data);
                $product = Product::find($productid);
                $product->clearCache();
                if ($product) {
                    return array("status" => "success", "message" => "product updated", "product" => $product);
                }
            } else {
                $merchantid = $data['merchant_id'];
                unset($data['merchant_id']);
                $data['isActive'] = false;
                $data['user_id'] = $user->id;
                $data = (object) array_filter((array) $data, function ($val) {
                            return !is_null($val);
                        });
                $data = (array) $data;
                $product = Product::create($data);
                $merchant = Merchant::find($merchantid);
                if ($merchant) {
                    if ($merchant->user_id == $user->id) {
                        $merchant->products()->save($product);
                    }
                }
                return array("status" => "success", "message" => "product created", "product" => $product);
            }
        } else {
            return array("status" => "error", "message" => "access denied");
        }
    }

    public function createOrUpdateVariant(User $user, array $data) {
        $result = $this->writeAccessProduct($user, $data['product_id']);
        if ($result['access'] == true) {
            if ($data["id"]) {
                $variantid = $data['id'];
                $productid = $data['product_id'];
                unset($data['id']);
                unset($data['merchant_id']);
                unset($data['product_id']);
                $data = (object) array_filter((array) $data, function ($val) {
                            return !is_null($val);
                        });
                $data = (array) $data;
                ProductVariant::where('id', $variantid)
                        ->where('product_id', $productid)
                        ->update($data);
                $variant = ProductVariant::find($variantid);
                if ($variant) {
                    return array("status" => "success", "message" => "variant updated", "variant" => $variant);
                }
            } else {
                $data = (object) array_filter((array) $data, function ($val) {
                            return !is_null($val);
                        });
                $data = (array) $data;
                $variant = ProductVariant::create($data);
                return array("status" => "success", "message" => "variant created", "variant" => $variant);
            }
        }
        return array("status" => "error", "message" => "Access denied");
    }

    public function changeProductOwners(User $user, array $data) {
        $result = $this->writeAccessProduct($user, $data['product_id']);
        if ($result['access'] == true) {
            $merchants = $data['merchants'];
            $product = Product::find($data['product_id']);
            $operation = $data['operation'];
            foreach ($merchants as $value) {
                $merchant = Merchant::find($value);
                if ($merchant) {
                    if ($merchant->user_id) {
                        if ($operation == 'add') {
                            $merchant->products()->save($product);
                        } else if ($operation == 'remove') {
                            $merchant->products()->detach($product);
                        }
                    }
                }
            }
            return array("status" => "success", "message" => "Owners updated");
        }
        return array("status" => "error", "message" => "Missing required merchant id or product id");
    }

}
