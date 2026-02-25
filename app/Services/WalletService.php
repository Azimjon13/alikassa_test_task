<?php
namespace App\Services;

use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class WalletService
{
    public function deposit(User $user, float $amount, string $txHash): Transaction
    {
        return DB::transaction(function () use ($user,$amount,$txHash){

            if (Transaction::where('tx_hash',$txHash)->exists()) {
                throw new Exception('Duplicate tx');
            }

            Balance::firstOrCreate(['user_id'=>$user->id]);

            return Transaction::create([
                'user_id'=>$user->id,
                'amount'=>$amount,
                'type'=>'deposit',
                'status'=>'pending',
                'tx_hash'=>$txHash
            ]);
        });
    }

    public function confirmDeposit(string $txHash): Transaction
    {
        return DB::transaction(function () use ($txHash){

            $tx = Transaction::where('tx_hash',$txHash)
                ->lockForUpdate()
                ->firstOrFail();

            if ($tx->status === 'confirmed') return $tx;

            $balance = Balance::where('user_id',$tx->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $balance->available += $tx->amount;
            $balance->save();

            $tx->status='confirmed';
            $tx->confirmations=6;
            $tx->save();

            return $tx;
        });
    }

    public function withdraw(User $user, float $amount): Transaction
    {
        return DB::transaction(function () use ($user,$amount){

            $balance = Balance::where('user_id',$user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($balance->available < $amount) {
                throw new Exception('Insufficient funds');
            }

            $balance->available -= $amount;
            $balance->locked += $amount;
            $balance->save();

            return Transaction::create([
                'user_id'=>$user->id,
                'amount'=>$amount,
                'type'=>'withdraw',
                'status'=>'pending'
            ]);
        });
    }

    public function confirmWithdraw(Transaction $tx): Transaction
    {
        return DB::transaction(function () use ($tx){

            $tx = Transaction::lockForUpdate()->findOrFail($tx->id);

            if ($tx->status === 'confirmed') return $tx;

            $balance = Balance::where('user_id',$tx->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $balance->locked -= $tx->amount;
            $balance->save();

            $tx->status='confirmed';
            $tx->save();

            return $tx;
        });
    }
}