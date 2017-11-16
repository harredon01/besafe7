<?php

namespace App\Services;

use Validator;
use App\Models\FileM;
use App\Models\User;
use App\Models\Item;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Services\EditFile;
use App\Services\EditMerchant;
use Cache;
use DB;

class EditProduct {

    const OBJECT_MERCHANT = 'Merchant';
    const OBJECT_PRODUCT = 'Product';

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
    protected $editMerchant;

    /**
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditFile $editFile,EditMerchant $editMerchant) {
        $this->editFile = $editFile;
        $this->editMerchant = $editMerchant;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProduct(User $user, $product_id) {
        $data = [];
        $product = Product::find($product_id);
        if ($product) {
            $result = $this->checkAccess($user, $product->merchant_id, self::OBJECT_MERCHANT);
            if ($result['access']) {
                if ($result['owner_id'] == $user->id) {
                    $product->mine = true;
                }
                $data['product'] = $product;
                $data['variants'] = $product->productVariants;
                $data['files'] = FileM::where("type", self::OBJECT_PRODUCT)->where("trigger_id", $product->id)->get();
            }
        }
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function getProductsMerchant(User $user, $merchant_id) {
        $data = [];
        $result = $this->checkAccess($user, $merchant_id, self::OBJECT_MERCHANT);
        if ($result['access']) {
            $data = Cache::remember('products_merchant_' . $merchant_id, 100, function ()use ($merchant_id) {
                        $data['products_variants'] = DB::table('products')
                                ->join('product_variant', 'products.id', '=', 'product_variant.product_id')
                                ->where('products.merchant_id', $merchant_id)
                                ->select('products.*', 'product_variant.*')
                                ->get();
                        $data['products_files'] = DB::table('products')
                                ->leftJoin('files', 'products.id', '=', 'files.trigger_id')
                                ->where('files.type', "Product")
                                ->where('products.merchant_id', $merchant_id)
                                ->select('products.*', 'files.*')
                                ->get();
                        return $data;
                    });
        }

        return $data;
    }

    public function getVariant(User $user, $variantId) {
        $data = [];
        $variant = ProductVariant::find($variantId);
        if ($variant) {
            $result = $this->checkAccess($user, $variant->merchant_id, self::OBJECT_MERCHANT);
            if ($result['access']) {
                $data['variant'] = $variant;
            }
        }
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function checkAccess(User $user, $merchant_id, $type) {
        $merchant = Merchant::find($merchant_id);
        $access = false;
        if ($merchant) {
            if ($merchant->is_public) {
                $access = true;
            } else {
                $group = $merchant->group;
                if ($group) {
                    $data = [];
                    $data = $this->editMerchant->checkGroupStatus($user, $group, $data);
                    if ($data) {
                        $access = true;
                    }
                }
                if ($user) {
                    $members = DB::select('select user_id as id from userables where user_id  = ? and userable_type = ? and object_id = ? ', [$user->id, $type, $merchant->id]);
                    if (sizeof($members) > 0) {
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
    public function deleteProduct(User $user, $productId) {
        $product = Product::find($productId);
        if ($product) {
            $merchant = $product->merchant;
            if ($merchant->user_id == $user->id) {
                $variants = $product->productVariants;
                $product->conditions()->delete();
                $files = FileM::where("type", self::OBJECT_PRODUCT)->where("trigger_id", $product->id)->get();
                foreach ($files as $file) {
                    $this->editFile->delete($user, $file->id);
                }
                foreach ($variants as $variant) {
                    Item::where('product_variant_id', $variant->id)->delete();
                    $variant->delete();
                }
                
                Cache::forget('products_merchant_' . $merchant->id);
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
            $product = $variant->product;
            if ($product) {
                $merchant = $product->merchant;
                if ($merchant->user_id == $user->id) {
                    $variant->conditions()->delete();
                    $variant->items()->delete();
                }
                Cache::forget('products_merchant_' . $merchant->id);
            }
        }
    }

    public function createOrUpdateProduct(User $user, array $data) {
        if ($data['merchant_id']) {
            $merchant = Merchant::find($data['merchant_id']);
            if ($merchant) {
                if ($merchant->user_id == $user->id) {
                    if ($data["id"]) {
                        $productid = $data['id'];
                        $merchantid = $data['merchant_id'];
                        unset($data['id']);
                        unset($data['merchant_id']);
                        $data = (object) array_filter((array) $data, function ($val) {
                                    return !is_null($val);
                                });
                        $data = (array) $data;
                        Product::where('id', $productid)->where('merchant_id', $merchantid)->update($data);
                        $product = Product::find($productid);
                        if ($product) {
                            Cache::forget('products_merchant_' . $merchantid);
                            return array("status" => "success", "message" => "product updated", "product" => $product);
                        }
                    } else {
                        $data['isActive'] = false;
                        $data = (object) array_filter((array) $data, function ($val) {
                                    return !is_null($val);
                                });
                        $data = (array) $data;
                        $product = Product::create($data);
                        Cache::forget('products_merchant_' . $merchant->id);
                        return array("status" => "success", "message" => "product created", "product" => $product);
                    }
                }
                return array("status" => "error", "message" => "not your merchant");
            }
            return array("status" => "error", "message" => "Merchant doesnt exist");
        }
        return array("status" => "error", "message" => "Missing required merchant id");
    }

    public function createOrUpdateVariant(User $user, array $data) {
        if ($data['merchant_id'] && $data['product_id']) {
            $merchant = Merchant::find($data['merchant_id']);
            if ($merchant) {
                if ($merchant->user_id == $user->id) {
                    $results = $merchant->products()->where('id', $data['product_id'])->get();
                    if (count($results) > 0) {

                        if ($data["id"]) {
                            $variantid = $data['id'];
                            $productid = $data['product_id'];
                            $merchantid = $data['merchant_id'];
                            unset($data['id']);
                            unset($data['merchant_id']);
                            unset($data['product_id']);
                            $data = (object) array_filter((array) $data, function ($val) {
                                        return !is_null($val);
                                    });
                            $data = (array) $data;
                            ProductVariant::where('id', $variantid)
                                    ->where('product_id', $productid)
                                    ->where('merchant_id', $merchantid)
                                    ->update($data);
                            $variant = ProductVariant::find($variantid);
                            if ($variant) {
                                Cache::forget('products_merchant_' . $merchantid);
                                return array("status" => "success", "message" => "variant updated", "variant" => $variant);
                            }
                        } else {
                            $data = (object) array_filter((array) $data, function ($val) {
                                        return !is_null($val);
                                    });
                            $data = (array) $data;
                            $variant = ProductVariant::create($data);
                            Cache::forget('products_merchant_' . $merchant->id);
                            return array("status" => "success", "message" => "variant created", "variant" => $variant);
                        }
                    }
                    return array("status" => "error", "message" => "Product doesnt exist");
                }
                return array("status" => "error", "message" => "not your merchant");
            }
            return array("status" => "error", "message" => "Merchant doesnt exist");
        }
        return array("status" => "error", "message" => "Missing required merchant id or product id");
    }

}
