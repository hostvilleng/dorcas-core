<?php /** @noinspection ALL */


namespace App\Http\Controllers\Transtrak;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class Setup extends Controller
{

    public function index()
    {
        $company = $this->company();
        $mailExist = DB::table('transtrak_mails')->where('transtrak_mail',$company->prefix.'@transtrak.dorcas.io')->exists();
        if (empty($company->prefix) && !$mailExist) {
            throw new NotFoundHttpException('A prefix has not been generated for this company yet');
        }
        return response()->json(['status'=>'success','message'=>'prefix has been generated for this company'],200);
    }

    public function create(){
        $company = $this->company();
        if (empty($company->prefix)){
            $company->prefix = prefixGenerator();
            $company->save();
        }
        $mailExist = DB::table('transtrak_mails')->where('transtrak_mail',$company->prefix.'@transtrak.dorcas.io')->exists();
        if(!$mailExist){
            return 'not allowed';
            throw new MethodNotAllowedException('You already have a transtrak email');
        }
        DB::table('transtrak_mails')->insert(['company_id'=>$company->id,'transtrak_mail'=>$company->prefix.'@transtrak.dorcas.io']);
        return response()->json(['status'=>'success','message'=>'transtrak mail has been setup successfully '],201);

    }

}


