<?php

use Illuminate\Database\Seeder;
use App\Services\Proofhub;
use App\Services\PayU;
use App\Services\EditOrderFood;
use App\Services\EditAlerts;
use App\Models\Item;
use App\Jobs\ProofhubJob;
use App\Models\Order;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Route;
use App\Models\OrderAddress;

class ProofhubSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    const ORDER_PAYMENT = 'order_payment';

    /**
     * The edit profile implementation.
     *
     */
    protected $editOrderFood;

    /**
     * The edit profile implementation.
     *
     */
    protected $food;

    public function __construct(Proofhub $proofhub) {
        $this->proofhub = $proofhub;
    }

    public function run() {
        /*$users = User::whereIn("id",[4,5,7,8,9,10,11,12,13,14,15,16,17,18,19,20])->get();
        foreach ($users as $user) {
            $user->merchants()->delete();
            $user->addresses()->delete();
            $user->push()->delete();
            $user->locations()->delete();
            //$user->orders()->detach();
            foreach ($user->orders as $item) {
                $item->user_id = 3;
                $item->save();
            }
            foreach ($user->payments as $item) {
                $item->user_id = 3;
                $item->save();
            }
            foreach ($user->deliveries as $item) {
                $item->user_id = 3;
                $item->save();
            }
            foreach ($user->deliveries as $item) {
                $item->user_id = 3;
                $item->save();
            }
            OrderAddress::where("user_id", $user->id)->update(["user_id" => 3]);
            App\Models\Transaction::where("user_id", $user->id)->update(["user_id" => 3]);
            $user->notifications()->delete();
            $user->items()->delete();
            $user->sources()->delete();
            $user->messages()->delete();
            $user->delete();
        }

        return true;*/
        $this->proofhub->getReport();
        //dispatch(new ProofhubJob()); 
    }

    public function deleteOldData() {
        $deliveries = Delivery::where("user_id", 1)->get();
        foreach ($deliveries as $item) {
            DB::table('delivery_stop')
                    ->where('delivery_id', $item->id)
                    ->delete();
            DB::table('delivery_route')
                    ->where('delivery_id', $item->id)
                    ->delete();
            $item->delete();
        }
        $routes = Route::where("status", "pending")->get();
        foreach ($routes as $value) {
            $value->stops()->delete();
        }
        $routes = Route::where("status", "pending")->delete();

        OrderAddress::where("name", "test")->delete();
    }

}
