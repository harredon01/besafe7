<?php

namespace App\Services;

use Validator;
use App\Models\Order;
use App\Models\User;
use App\Models\Item;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Merchant;
use App\Services\EditFile;
use Mail;
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
     * Create a new class instance.
     *
     * @param  EventPusher  $pusher
     * @return void
     */
    public function __construct(EditFile $editFile) {
        $this->editFile = $editFile;
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
            if ($result) {
                if ($product->merchant_id == $user->id) {
                    $product->mine = true;
                }
                $data['product'] = $product;
                $data['variants'] = $product->productVariants;
                $data['files'] = FileM::where("type", self::OBJECT_PRODUCT)->where("trigger_id", $product->id)->get();
            }
        }
        return $data;
    }

    public function getVariant(User $user, $variantId) {
        $data = [];
        $variant = ProductVariant::find($variantId);
        if ($variant) {
            $result = $this->checkAccess($user, $variant->merchant_id, self::OBJECT_MERCHANT);
            if ($result) {
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
        if ($merchant) {
            if ($merchant->is_public) {
                return true;
            } else {
                $group = $merchant->group;
                if ($group) {
                    $data = [];
                    $data = $this->checkGroupStatus($user, $group, $data);
                    if ($data) {
                        return true;
                    }
                }
                if ($user) {
                    $members = DB::select('select user_id as id from userables where user_id  = ? and userable_type = ? and object_id = ? ', [$user->id, $type, $merchant->id]);
                    if (sizeof($members) > 0) {
                        return true;
                    }
                }
            }
        }
        return false;
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
                }
                $variants->delete();
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
            }
        }
    }

    public function createOrUpdateProduct(User $user, array $data) {
        if ($data['merchant_id']) {
            $merchant = Merchant::find($data['merchant_id']);
            if ($merchant) {
                if ($merchant->user_id == $user->id) {
                    $data = (object) array_filter((array) $data, function ($val) {
                                return !is_null($val);
                            });
                    $data = (array) $data;
                    if ($data["id"]) {
                        $productid = $data['id'];
                        $merchantid = $data['merchant_id'];
                        unset($data['id']);
                        unset($data['merchant_id']);
                        Product::where('id', $productid)->where('merchant_id', $merchantid)->update($data);
                        $product = Product::find($productid);
                        if ($product) {
                            return array("status" => "success", "message" => "product updated", "product" => $product);
                        }
                    } else {
                        $data['isActive'] = false;
                        $product = Product::create($data);
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
                        $data = (object) array_filter((array) $data, function ($val) {
                                    return !is_null($val);
                                });
                        $data = (array) $data;
                        if ($data["id"]) {
                            $variantid = $data['id'];
                            $productid = $data['product_id'];
                            $merchantid = $data['merchant_id'];
                            unset($data['id']);
                            unset($data['merchant_id']);
                            unset($data['product_id']);

                            ProductVariant::where('id', $variantid)
                                    ->where('product_id', $productid)
                                    ->where('merchant_id', $merchantid)
                                    ->update($data);
                            $variant = ProductVariant::find($variantid);
                            if ($variant) {
                                return array("status" => "success", "message" => "variant updated", "variant" => $variant);
                            }
                        } else {
                            $variant = ProductVariant::create($data);
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
