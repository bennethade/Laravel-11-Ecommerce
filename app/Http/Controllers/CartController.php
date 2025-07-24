<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function index()
    { 
        $items = Cart::instance('cart')->content();
        return view('cart', compact('items'));

    }

    public function addToCart(Request $request)
    {
        Cart::instance('cart')->add($request->id, $request->name, $request->quantity, $request->price)->associate('App\Models\Product');
        return redirect()->back();
    }


    public function increaseCartQuantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty + 1;
        Cart::instance('cart')->update($rowId, $qty);

        return redirect()->back();
    }


    public function decreaseCartQuantity($rowId)
    {
        $product = Cart::instance('cart')->get($rowId);
        $qty = $product->qty - 1;
        Cart::instance('cart')->update($rowId, $qty);

        return redirect()->back();
    }


    public function removeItem($rowId)
    {
        Cart::instance('cart')->remove($rowId);
        return redirect()->back();
    }


    public function emptyCart()
    {
        Cart::instance('cart')->destroy();
        return redirect()->back();
    }


    public function applyCouponCode(Request $request)
    {
        $couponCode = $request->coupon_code;
        if(isset($couponCode))
        {
            $cartSubtotal = (float) str_replace(',', '', Cart::instance('cart')->subtotal());

            $coupon = Coupon::where('code', $couponCode)
                ->where('expiry_date', '>=', Carbon::today())
                ->where('cart_value', '<=', $cartSubtotal) // Now comparing numbers
                ->first();

            if(!$coupon)
            {
                return redirect()->back()->with('error', 'Invalid Coupon Code!');
            }
            else{
                Session::put('coupon', [
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'cart_value' => $coupon->cart_value,
                ]);
                $this->calculateDiscount();
                return redirect()->back()->with('success', 'Coupon has been applied!');
            }
        }
        else{
            return redirect()->back()->with('error', 'Invalid Coupon Code!');
        }
    }


    public function calculateDiscount()
    {
        $discount = 0;
        if(Session::has('coupon'))
        {
            $cartSubtotal = (float) str_replace(',', '', Cart::instance('cart')->subtotal());
            
            if(Session::get('coupon')['type'] == 'fixed')   
            {
                $discount = Session::get('coupon')['value'];
            }
            else {
                $discount = ($cartSubtotal * Session::get('coupon')['value']) / 100;
            }

            $subtotalAfterDiscount = $cartSubtotal - $discount;
            $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;
            $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

            Session::put('discounts', [
                'discount' => number_format($discount, 2, '.', ''),
                'subtotal' => number_format($subtotalAfterDiscount, 2, '.', ''),
                'tax' => number_format($taxAfterDiscount, 2, '.', ''),
                'total' => number_format($totalAfterDiscount, 2, '.', '')
            ]);
        }
    }


    public function removeCouponCode()
    {
        Session::forget('coupon');
        Session::forget('discounts');
        return back()->with('success', 'Coupon has been removed!');
    }


    public function checkout()
    {
        if(!Auth::check())
        {
            return redirect()->route('login');
        }
        $address = Address::where('user_id', Auth::user()->id)->where('isdefault', 1)->first();
        return view('checkout', compact('address'));
    }


    public function placeAnOrder(Request $request)
    {
        $user_id = Auth::user()->id;
        $address = Address::where('user_id', $user_id)->where('isdefault', true)->first();

        if(!$address)
        {
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:11',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required',
            ]);

            $address = new Address();
            $address->name  =   $request->name;
            $address->phone =   $request->phone;
            $address->zip   =   $request->zip;
            $address->state =   $request->state;
            $address->city  =   $request->city;
            $address->address   =   $request->address;
            $address->locality  =   $request->locality;
            $address->landmark  =   $request->landmark;
            $address->country   =   'Nigeria';
            $address->user_id   =   $user_id;
            $address->isdefault =  true;

            $address->save();
        }

        $this->setAmountForCheckout();

        $order = new Order();

        $order->user_id     =   $user_id;
        $order->subtotal    =   Session::get('checkout')['subtotal'];
        $order->discount    =   Session::get('checkout')['discount'];
        $order->tax         =   Session::get('checkout')['tax'];
        $order->total       =   Session::get('checkout')['total'];   
        $order->name        =   $address->name;
        $order->phone       =   $address->phone;
        $order->locality    =   $address->locality;
        $order->address     =   $address->address;
        $order->city        =   $address->city;
        $order->state       =   $address->state;
        $order->country     =   $address->country;
        $order->landmark    =   $address->landmark;
        $order->zip         =   $address->zip;

        $order->save();

        foreach(Cart::instance('cart')->content() as $item)
        {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();
        }

        if($request->mode == "card")
        {
            //
        }
        elseif($request->mode == "paypal")
        {
            //
        }
        elseif($request->mode == "cod")
        {
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = "pending";
            $transaction->save();
        }

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);

        // return view('order_confirmation', compact('order'));
        return redirect()->route('cart.order.confirmation');
    }


    protected function cleanNumber($value) 
    {
        if (is_string($value)) {
            return str_replace([',', ' '], '', $value);
        }
        return $value;
    }

    public function setAmountForCheckout()
    {
        if(!Cart::instance('cart')->content()->count() > 0) {
            Session::forget('checkout');
            return;
        }

        if(Session::has('coupon')) {
            Session::put('checkout', [
                'discount' => $this->cleanNumber(Session::get('discounts')['discount']),
                'subtotal' => $this->cleanNumber(Session::get('discounts')['subtotal']),
                'tax' => $this->cleanNumber(Session::get('discounts')['tax']),
                'total' => $this->cleanNumber(Session::get('discounts')['total']),
            ]);
        } else {
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => $this->cleanNumber(Cart::instance('cart')->subtotal()),
                'tax' => $this->cleanNumber(Cart::instance('cart')->tax()),
                'total' => $this->cleanNumber(Cart::instance('cart')->total()),
            ]);
        }
    }


    // public function setAmountForCheckout()
    // {
    //     if(!Cart::instance('cart')->content()->count() > 0)
    //     {
    //         Session::forget('checkout');
    //         return;
    //     }

    //     if(Session::has('coupon'))
    //     {
    //         Session::put('checkout', [
    //             'discount' => Session::get('discounts')['discount'],
    //             'subtotal' => Session::get('discounts')['subtotal'],
    //             'tax' => Session::get('discounts')['tax'],
    //             'total' => Session::get('discounts')['total'],
    //         ]);
    //     }
    //     else{
    //             Session::put('checkout', [
    //             'discount' => 0,
    //             'subtotal' => Cart::instance('cart')->subtotal(),
    //             'tax' => Cart::instance('cart')->tax(),
    //             'total' => Cart::instance('cart')->total(),
    //         ]);
    //     }
    // }


    public function orderConfirmation()
    {
        if(Session::has('order_id'))
        {
            $order = Order::find(Session::get('order_id'));
            return view('order_confirmation', compact('order'));    
        }
        return redirect()->route('cart.index');
    }






}
