<?php

namespace App\Http\Controllers\Api\v1\TrackManageController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyConfig;
use App\Http\StaticFunctions\StaticFunctions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Client;
use App\ClientJob;
use App\ClientService;
use App\ClientDeadline;
use App\Http\Resources\TrackManager\TrackManagerResource;

class TrackManageController extends Controller
{
	public function getAllDeadlines(Request $req)
	{
		$FilterRequest = ['company_id','module_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
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
            	$deadline = ClientJob::with('client.code','get_service')->where('job_status','<>',ClientJob::JOB_COMPLETED)->where('job_status','<>',ClientJob::JOB_CANCELLED)->orderBy('due_date','asc')->get();
            	if(!empty($deadline)) {
                  $allDeadline = TrackManagerResource::collection($deadline);
            		return Response::json(['status' => 'success', 'data' => $allDeadline]);
            	} else {
            		$errors['ErrorMessage'] = ['ClientJob Not Found !!'];
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

	public function editAssignedJobsView(Request $req)
	{
		$FilterRequest = ['company_id','module_id','job_id','cs_id'];
      $data = $req->only($FilterRequest);

      // changing the input fields name
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'job_id' => 'Job ID',
         'cs_id' => 'Client Service ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'job_id' => 'required|numeric',
         'cs_id' => 'required|numeric',
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
            	$clientJob = ClientJob::with('client.code','get_service')->where(['job_id'=>$data['job_id'],'cs_id'=>$data['cs_id']])->first();
            	if(!empty($clientJob)) {
                  $getClientJob = new TracKManagerResource($clientJob);
            		return Response::json(['status' => 'success', 'data' => $getClientJob]);
            	} else {
            		$errors['ErrorMessage'] = ['ClientJob Not Found !!'];
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

	public function updateAssignedJobsView(Request $req)
	{
		$FilterRequest = ['company_id','module_id','job_id','cs_id','due_date','assigned_to','assigned_due_date','job_status','client_id','can_contact_client','is_documents_received','comments'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'client_id' => 'Client ID',
         'job_id' => 'Job ID',
         'cs_id' => 'Client Service ID',
         'due_date' => 'Due Date',
         'assigned_due_date' => 'Assigned Due Date',
         'can_contact_client' => 'Can Contact Client',
         'is_documents_received' => 'Is Documents Received',
         'assigned_to' => 'Assigned To',
         'job_status' => 'Job Status',
         'comments'=>'Comments'
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'client_id' => 'required|string',
         'job_id' => 'required|numeric',
         'cs_id' => 'required|numeric',
         'assigned_due_date' => 'required|date',
         'assigned_to' => 'required|numeric',
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
            	$assignedJobs = ClientJob::where(['job_id'=>$data['job_id'],'cs_id'=>$data['cs_id'],'client_id'=>$data['client_id']])->update([
			         'assigned_due_date' =>StaticFunctions::dateRequets($data['assigned_due_date']),
			         'assigned_to' =>$data['assigned_to'],
			         'assigned_date' =>date('Y-m-d'),
			         'can_contact_client' =>$data['can_contact_client'],
			         'is_documents_received' =>$data['is_documents_received'],
			         'comments' =>$data['comments'],
			         'job_status' =>ClientJob::JOB_ASSIGNED,
            	]);
            	if(!empty($assignedJobs)) {
            		$success['SuccessMessage'] = ['Assigned Jobs Successfully Add !!'];
            		return Response::json(['status' => 'success', 'data' => $success]);
            	} else {
            		$errors['ErrorMessage'] = ['Failled to Add Assigned Jobs !!'];
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

	public function updateCompleteJobsView(Request $req)
	{
		$FilterRequest = ['company_id','module_id','client_id','job_id','cs_id','completed_by'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'client_id' => 'Client ID',
         'job_id' => 'Job ID',
         'cs_id' => 'Client Service ID',
         'completed_by' => 'Completed By',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'client_id' => 'required|string',
         'job_id' => 'required|numeric',
         'cs_id' => 'required|numeric',
         'completed_by' => 'required|numeric',
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
            	$complete = ClientJob::where(['job_id'=>$data['job_id'],'cs_id'=>$data['cs_id'],'client_id'=>$data['client_id']])->first();
            	$completeJobs = ClientJob::where(['job_id'=>$data['job_id'],'cs_id'=>$data['cs_id'],'client_id'=>$data['client_id']])->update([
			         'job_status' =>ClientJob::JOB_COMPLETED,
			         'completed_by' =>$data['completed_by'],
			         'completed_on' =>now(),
            	]);
            	if(!empty($complete)) {
            		if($completeJobs)
            		{
            			$YearEnd =strtotime($complete->year_end);
            			$DueDate =strtotime($complete->due_date);
            			$AddYearEnd = strtotime('+ 1 year', $YearEnd);
            			$AddDueDate = strtotime('+ 1 year', $DueDate);
            			ClientJob::create([
					         'job_status' =>ClientJob::JOB_NEW,
					         'year_end' =>date('Y-m-d', $AddYearEnd),
					         'year_end' =>date('Y-m-d', $AddYearEnd),
					         'due_date' =>date('Y-m-d', $AddDueDate),
					         'client_id' =>$complete->client_id,
					         'cs_id' =>$complete->cs_id,
					         'module_id' =>StaticFunctions::getModuleSlugByID($data['module_id']),
		            	]);

		            	$success['SuccessMessage'] = ['Completed Jobs Successfully  !!'];
            			return Response::json(['status' => 'success', 'data' => $success]);
            		}
            	} else {
            		$errors['ErrorMessage'] = ['Failled to Completed Jobs !!'];
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

	public function updateCancelJobsView(Request $req)
	{
		$FilterRequest = ['company_id','module_id','client_id','job_id','cs_id','completed_by'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'client_id' => 'Client ID',
         'job_id' => 'Job ID',
         'cs_id' => 'Client Service ID',
         'completed_by' => 'Completed By',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'client_id' => 'required|string',
         'job_id' => 'required|numeric',
         'cs_id' => 'required|numeric',
         'completed_by' => 'required|numeric',
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
            	$cancelledJobs = ClientJob::where(['job_id'=>$data['job_id'],'cs_id'=>$data['cs_id'],'client_id'=>$data['client_id']])->update([
			         'job_status' =>ClientJob::JOB_CANCELLED,
			         'completed_by' =>$data['completed_by'],
            	]);
            	if(!empty($cancelledJobs)) {
            		$success['ErrorMessage'] = ['Cancelled Jobs Successfully !!'];
            		return Response::json(['status' => 'success', 'data' => $success]);
            	} else {
            		$errors['ErrorMessage'] = ['Failled to Cancelled Jobs !!'];
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
	

	public function getCanceljobs(Request $req)
	{
		$FilterRequest = ['company_id','module_id'];
      $data = $req->only($FilterRequest);

      // changing the input fields name
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
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
            	$deadline = ClientJob::with('client.code','get_service')->where(['job_status'=>ClientJob::JOB_CANCELLED])->orderBy('due_date','asc')->get();
            	$cancelJobs = TrackManagerResource::collection($deadline);
            	if(!empty($cancelJobs)) {
            		return Response::json(['status' => 'success', 'data' => $cancelJobs]);
            	} else {
            		$errors['ErrorMessage'] = ['ClientJob Not Found !!'];
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
	public function getAssignedjobs(Request $req)
	{
		$FilterRequest = ['company_id','module_id'];
      $data = $req->only($FilterRequest);

      // changing the input fields name
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
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
            	$deadline = ClientJob::with('client.code','get_service')->where(['job_status'=>ClientJob::JOB_ASSIGNED])->orderBy('due_date','asc')->get();
            	$assignedJobs = TrackManagerResource::collection($deadline);
            	if(!empty($assignedJobs)) {
            		return Response::json(['status' => 'success', 'data' => $assignedJobs]);
            	} else {
            		$errors['ErrorMessage'] = ['ClientJob Not Found !!'];
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
	public function getCompletedjobs(Request $req)
	{
		$FilterRequest = ['company_id','module_id'];
      $data = $req->only($FilterRequest);

      // changing the input fields name
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
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
            	$deadline = ClientJob::with('client.code','get_service')->where(['job_status'=>ClientJob::JOB_COMPLETED])->orderBy('due_date','asc')->get();
            	$completedJobs = TrackManagerResource::collection($deadline);
            	if(!empty($completedJobs)) {
            		return Response::json(['status' => 'success', 'data' => $completedJobs]);
            	} else {
            		$errors['ErrorMessage'] = ['ClientJob Not Found !!'];
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
