<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WalletService;

class WalletController extends Controller
{
    public function deposit(Request $request, WalletService $wallet)
    {
        return $wallet->deposit(
            $request->user(),
            (float)$request->amount,
            $request->tx_hash
        );
    }

    public function withdraw(Request $request, WalletService $wallet)
    {
        return $wallet->withdraw(
            $request->user(),
            (float)$request->amount
        );
    }
}