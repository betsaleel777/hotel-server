<?php
namespace App\Http\Controllers\Caisse;

use App\Http\Controllers\Controller;
use App\Models\Caisse\MobileMoney;
use Illuminate\Http\Request;

class MobileMoneyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function getAll()
    {
        $mobilesMoney = MobileMoney::get();
        return response()->json(['mobiles' => $mobilesMoney]);
    }

    public function insert(Request $request)
    {

    }

    public function getOne(int $id)
    {

    }

    public function update(Request $request)
    {

    }

    public function delete()
    {

    }
}
