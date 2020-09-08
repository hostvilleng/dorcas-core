<?php /** @noinspection ALL */


namespace App\Http\Controllers\Finance;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Psy\Exception\ErrorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Transtrak extends Controller
{

    public function index()
    {
        $company = $this->company();
        $mailExist = DB::table('transtrak_mails')->where(['transtrak_mail'=>$company->prefix.'@transtrak.dorcas.io','company_id' => $company->id])->exists();
        if (empty($company->prefix) || !$mailExist) {
            throw new NotFoundHttpException('A prefix has not been generated for this company yet');
        }
        $data = ['status'=>'success','message'=>'prefix has been generated for this company','transtrak_mail' => $company->prefix.'@transtrak.dorcas.io'];
        return response()->json($data,200);
    }

    public function create(){
        $company = $this->company();
        if (empty($company->prefix)){
            $company->prefix = prefixGenerator();
            $company->save();
        }
        $mailExist = DB::table('transtrak_mails')->where('transtrak_mail',$company->prefix.'@transtrak.dorcas.io')->exists();
        if($mailExist){
            throw new \ErrorException('You already have a transtrak email');
        }
        DB::table('transtrak_mails')->insert(['company_id'=>$company->id,'transtrak_mail'=>$company->prefix.'@transtrak.dorcas.io']);
        return response()->json(['status'=>'success','message'=>'transtrak mail has been setup successfully '],201);

    }

}


