# Laravel Crypto Wallet Test

Simple crypto balance module.

Features:

- async deposit (pending -> confirmed)
- withdraw with locked funds
- idempotency via tx_hash
- DB transactions + row locking

Stack: PHP, Laravel 8, MySQL