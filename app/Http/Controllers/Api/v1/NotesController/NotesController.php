<?php

namespace App\Http\Controllers\Api\v1\NotesController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\UserRequest;
use App\Http\Resources\User as UserResource;
use Arr;
use App\OauthClient;
use App\Notes;
use DB;
use App\CompanyConfig;
use App\Http\Resources\Notes\NotesResource;
use App\Http\StaticFunctions\StaticFunctions;
use App\DatasbeseConnection;

class NotesController extends Controller
{
   public function NotesAdd(Request $req)
   {
      $FilterRequest = ['company_id','client_id','user_id','note_date','note_time','service_id','due_date','note_data','telephone_conversation','module_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company Id',
         'client_id' => 'Client Id',
         'user_id' => 'User Id',
         'note_date' => 'Note Date',
         'note_time' => 'Note Time',
         'service_id' => 'Service Id',
         'due_date'=> 'Due Date',
         'note_data'=> 'Note Data',
         'telephone_conversation' => 'Telephone Conversation',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'client_id' => 'required|numeric',
         'user_id' => 'required|numeric',
         'note_date' => 'required',
         'note_time' => 'required',
         'due_date' => 'required'
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }

      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
                $notes = Notes::create([
                    'client_id'=>$data['client_id'],
                    'user_id'=>$data['user_id'],
                    'note_date'=> StaticFunctions::dateRequets($data['note_date']),
                    'note_time'=>$data['note_time'],
                    'service_id'=>$data['service_id'],
                    'due_date'=>StaticFunctions::dateRequets($data['due_date']),
                    'note_data'=>$data['note_data'],
                    'telephone_conversation'=>$data['telephone_conversation'],
                    'module_id' => StaticFunctions::getModuleSlugByID($data['module_id']),
                ]);

                if(!empty($notes)) {
                    $succes['SuccessMessage'] = ['notes add succesfully.'];
                    return Response::json(['status' => 'success', 'data' => $succes]);
                } else {
                    $errors['ErrorMessage'] = ['Fail to add the notes, please try again.'];
                    return Response::json(['status' => 'error', 'data' => $errors]);
                }
            } else {
               $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
               return Response::json(['status' => 'error', 'data' => $errors]);
            }
         } else {
            $errors['ErrorMessage'] = ['Datasbese Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
         }
      } else {
         $errors['ErrorMessage'] = ['Company Not Found !!'];
         return Response::json(['status' => 'error', 'data' => $errors]);
      }
   }

    public function NotesList(Request $req) {
        $FilterRequest = ['company_id','module_id','client_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'module_id' => 'module id',
            'client_id' => 'client id'
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required',
            'module_id' => 'required',
            'client_id' => 'required'
        ])->setAttributeNames($attributes);
        if($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }
        $client_id = $data['client_id'];
        $moduleid = StaticFunctions::getModuleSlugByID($data['module_id']);
        $database = CompanyConfig::where('company_id',$data['company_id'])->get();
        $company_db = StaticFunctions::GetKeyValue($database,'company_database');
        $notes = DB::select("CALL getNotes('$company_db',$client_id,$moduleid)");
        $db_con = StaticFunctions::db_connection(strtolower($company_db));
        //$notes = Notes::where(['client_id'=>$req->client_id,'module_id'=>StaticFunctions::getgetModuleSlugByID($req->module_id)])->get();
        //$notesRresource =  NotesResource::collection($notes, 'my_new_data');

        $client_type = StaticFunctions::getClientTypeByID($req->client_id);

        if(count($notes)>0) {
            //$notes['ClientType'] = $client_type;
            return Response::json(['status' => 'success', 'data' => $notes]);
        } else {

            //$errors['ErrorMessage'] = ['Record Not Found !!'];
            $errors['ClientType'] = $client_type;
            $errors['ClientID'] = $req->client_id;
            return Response::json(['status' => 'error', 'data' => $errors]);
        }
    }

    public function NotesEdit(Request $req)
    {
        $FilterRequest = ['note_id','company_id','client_id','module_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'module_id' => 'Module ID',
            'note_id' => 'note id',

        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required',
            'note_id' => 'required',
            'module_id' => 'required',
        ])->setAttributeNames($attributes);

        if($validator->fails())
        {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

         $database = CompanyConfig::where('company_id',$data['company_id'])->get();
         if($database) {
            $company_db = StaticFunctions::GetKeyValue($database,'company_database');
            if($company_db) {
               $db_con = StaticFunctions::db_connection(strtolower($company_db));
               if($db_con) {
                  $notesEdit = Notes::where(['note_id'=>$data['note_id']])->first();
                   $new_arry = [
                  'NoteID' => $notesEdit->note_id,
                  'ClientID'=>$notesEdit->client_id,
                  'UserID' => $notesEdit->user_id,
                  'NoteDate' =>$notesEdit->note_date,
                  'NoteTime' =>$notesEdit->note_time,
                  'ServiceID' =>$notesEdit->service_id,
                  'DueDate' =>$notesEdit->due_date,
                  'NoteData' =>$notesEdit->note_data,
                  'TelephoneConversation' =>$notesEdit->telephone_conversation,
                  'ClientType' => $notesEdit->clientType->client_type,
                  'UserName' => StaticFunctions::getUserNameByID(!empty($notesEdit->user_id)?$notesEdit->user_id:'',$company_db)
               ];
                  if(!empty($new_arry))
                  {
                     return Response::json(['status' => 'success', 'data' => $new_arry]);
                  } else {
                     $error['ErrorMessage'] = ['Notes Not Found'];
                     return Response::json(['status' => 'error', 'data' => $error]);
                  }
               } else {
               $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
               return Response::json(['status' => 'error', 'data' => $errors]);
            }
         } else {
            $errors['ErrorMessage'] = ['Datasbese Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
         }
      } else {
         $errors['ErrorMessage'] = ['Company Not Found !!'];
         return Response::json(['status' => 'error', 'data' => $errors]);
      }
   }

    public function NotesUpdate(Request $req) {
        $FilterRequest = ['company_id','client_id','user_id','note_date','note_time','service_id','due_date','note_data','telephone_conversation'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'Company Id',
            'client_id' => 'Client Id',
            'user_id' => 'User Id',
            'note_date' => 'Note Date',
            'note_time' => 'Note Time',
            'service_id' => 'Service Id',
            'due_date'=> 'Due Date',
            'note_data'=> 'Note Data',
            'telephone_conversation' => 'Telephone Conversation',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required|numeric',
            'client_id' => 'required|numeric',
            'user_id' => 'required|numeric',
            'note_date' => 'required',
            'note_time' => 'required',
            'service_id' => 'required',
        ])->setAttributeNames($attributes);
        if($validator->fails()) {
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $data_get = CompanyConfig::where('company_id',$req->company_id)->get();
        if (count($data_get)>0) {
            $company_db = StaticFunctions::GetKeyValue($data_get, 'company_database');
            if(!empty($company_db)) {
                DatasbeseConnection::db_connection(strtolower($company_db));
                $update_data['client_id'] = $req->client_id;
                $update_data['user_id'] = $req->user_id;
                $update_data['note_date'] = $req->note_date;
                $update_data['note_time'] = $req->note_time;
                $update_data['service_id'] = $req->service_id;
                $update_data['due_date'] = $req->due_date;
                $update_data['note_data'] = $req->note_data;
                $update_data['telephone_conversation'] = $req->telephone_conversation;

                $return_data=Notes::where(['note_id'=>$req->note_id, 'client_id'=>$req->client_id])->update($update_data);

                if($return_data) {
                    $message['Message'] = ['Record updated'];
                    return Response::json(['status' => 'success', 'data' => $message]);
                } else {
                    $error['ErrorMessage'] = ['DataBase Not Found'];
                    return Response::json(['status' => 'success', 'data' => $error]);
                }

            } else {
                $error['ErrorMessage'] = ['Company does Not exist'];
                return Response::json(['status' => 'success', 'data' => $error]);
            }
        } else {
            $error['ErrorMessage'] = ['Database does Not exist'];
            return Response::json(['status' => 'success', 'data' => $error]);
        }

    }
    public function NotesDelete(Request $req) {
        $FilterRequest = ['company_id','note_id','client_id'];
        $data = $req->only($FilterRequest);
        $attributes = [
            'company_id' => 'company id',
            'note_id'=> 'contact id',
            'client_id' => 'client_id',
        ];
        $validator = Validator::make($data ,[
            'company_id' => 'required',
            'note_id' => 'required',
            'client_id' => 'required',
        ])->setAttributeNames($attributes);

        if($validator->fails()){
            return Response::json(['status' => 'error', 'data' => $validator->errors()]);
        }

        $data_get = CompanyConfig::where('company_id',$req->company_id)->get();
        $company_db = StaticFunctions::GetKeyValue($data_get, 'company_database');
        if(!empty($company_db)){
            DatasbeseConnection::db_connection(strtolower($company_db));
            $delete_data=Notes::where(['client_id'=>$req->client_id, 'note_id'=>$req->note_id])->delete();
            if($delete_data) {
                $success['Message'] = ['Data Deleted Succesfuly'];
                return Response::json(['status' => 'success', 'data' => $success]);
            }else{
                $errors['ErrorMessage'] = ['Record Not Deleted !!'];
                return Response::json(['status' => 'error', 'data' => $errors]);
            }
        } else {
            $error['ErrorMessage'] = ['DataBase Not Found'];
            return Response::json(['status' => 'error', 'data' => $error]);
        }
   }
   public function NotesListAll(Request $req) 
   {
      $FilterRequest = ['company_id','module_id','client_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
            'company_id' => 'Company ID',
            'module_id' => 'Module ID',
            'client_id' => 'Client ID'
      ];
      $validator = Validator::make($data ,[
            'company_id' => 'required',
            'module_id' => 'required',
            'client_id' => 'required'
      ])->setAttributeNames($attributes);

      if($validator->fails()) 
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }

      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               $notes = Notes::where(['client_id'=>$data['client_id']])->get();
               foreach ($notes as $key => $value) {
                  $new_arry = [
                  'NoteID' => $value->note_id,
                  'ClientID'=>$value->client_id,
                  'UserID' => $value->user_id,
                  'NoteDate' =>$value->note_date,
                  'NoteTime' =>$value->note_time,
                  'ServiceID' =>$value->service_id,
                  'DueDate' =>$value->due_date,
                  'NoteData' =>$value->note_data,
                  'TelephoneConversation' =>$value->telephone_conversation,
                  'ClientType' => $value->clientType->client_type,
                  'UserName' => StaticFunctions::getUserNameByID(!empty($value->user_id)?$value->user_id:'',$company_db)
               ];
                  $temp[] =$new_arry; 
               }
               // $get_data = NotesResource::collection($notes);
               if(!empty($temp)) {
                  return Response::json(['status' => 'success', 'data' => $temp]);
               } else {
                  $errors['ClientID'] = $data['client_id'];
                  return Response::json(['status' => 'error', 'data' => $errors]);
               }
            } else {
               $errors['ErrorMessage'] = ['Datasbese Connection Error !!'];
               return Response::json(['status' => 'error', 'data' => $errors]);
            }
         } else {
            $errors['ErrorMessage'] = ['Datasbese Not Found !!'];
            return Response::json(['status' => 'error', 'data' => $errors]);
         }
      } else {
         $errors['ErrorMessage'] = ['Company Not Found !!'];
         return Response::json(['status' => 'error', 'data' => $errors]);
      }
   }
}
