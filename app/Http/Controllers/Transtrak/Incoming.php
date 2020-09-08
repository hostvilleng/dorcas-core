<?php

namespace App\Http\Controllers\Transtrak;

use App\Http\Controllers\Controller;
use Aws\Credentials;
use Aws\Credentials\CredentialProvider;
use Aws\Exception\CredentialsException;
use Aws\S3\S3Client;
use App\Http\Controllers\Transtrak\PlancakeEmailParser as EmailParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class Incoming extends Controller
{
	public function process_incoming_email(Request $request)
	{
        $current_time = Carbon::now()->setTimezone('Africa/Lagos'); // ALL TIMEZONES: http://us.php.net/manual/en/timezones.others.php
        try {
            if ($request->has('fileName')) {
                $file_name = $request->input('fileName');

                // GET CREDENTIALS AND AUTHENTICATE
                $credentials = CredentialProvider::env();
                $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => 'eu-west-1',
//                    'credentials' => $credentials
                ]);
                // FECTH S3 OBJECT
                $object = $s3->GetObject([
                    'Bucket' => 'dorcas-files',
                    'Key' => $file_name,
                    ]);
//                $object = File::get(storage_path('app/emailBody.txt'));
                $body = $object['Body']->getContents();

                // return $body;
                // PARSE S3 OBJECT
                $parser = new EmailParser($body);
                $receivers = ['to' => $parser->getTo(), 'cc' => $parser->getCc()];

                $body_plain = $parser->getPlainBody();
                $body_html = $parser->getHTMLBody();
                $subject = $parser->getSubject();
                // PROCESS EACH RECEIVER
                foreach ($receivers as $type => $type_receivers) {
                    foreach ($type_receivers as $receiver) {
                        // PROCESS DOMAIN-MATCHING RECEIVERS
                        if (preg_match("/@(.*)/", $receiver, $matches) && $matches[1] == 'transtrak.dorcas.io') {
                            return $matches[0];
                        }
                        break;
                    }
                    break;
                }
            }
        }
        // ERROR TREATMENT
        catch (Exception $ex) {
            // DB::table('my-logs')->insert(
            //     ['sender' => $request->ip(), 'type' => 'error', 'content' => ($error_message = 'An exception occurred while processing an incoming email.') . ' Details: ' . $ex->getMessage()]
            // );
        }
    }

	public function logTranstrak(Request $request){
	  try{
          $data = [
              'payload' => $request->filename,
              'created_at' => Carbon::now(),
              'updated_at' => Carbon::now(),
          ];
          DB::table('transtrak_logs')->insert($data);
          return response()->json(['success'=>'logged message successfully'],201);
      }catch (Exception $ex){
	      Log::error($ex->getMessage());
      }
    }

}