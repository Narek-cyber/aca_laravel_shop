<?php

namespace App\Console\Commands;

use App\Jobs\UpdateProductRate;
use App\Models\Product;
use Illuminate\Console\Command;

class ProductRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Product::with('rate')->chunk(10, function ($flights) {
            foreach ($flights as $flight) {
//                echo $flight->id . ' : ' . round($flight->rate->avg('rate'), 2) . '<br>';

                dispatch(new UpdateProductRate($flight));
//
//                Product::query()->find($flight->id)->update([
//                    'rating' => round($flight->rate->avg('rate'), 2)
//                ]);
            }
        });
    }
}
