<?php
namespace Jiny\Modules\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Livewire\Attributes\On;

class LicenseStoreDetail extends Component
{
    public $code;

    public $plan_id;
    public $plan=[];

    public function render()
    {
        $row = DB::table('license_plan')
            ->where('code', $this->code)
            ->get();

        $this->plan = [];
        foreach($row as $item){
            $id = $item->id;
            $this->plan[$id] = get_object_vars($item);
        }
        //dd($this->plan);
        return view('jiny-modules::site.modules_detail.order', [
            //'row' => $row,
        ]);
    }

    public function choose($id)
    {
        $this->plan_id = $id;
    }


}
