<?php

namespace App\Http\Controllers\Api\v1\TimeSheetController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyConfig;
use App\Http\StaticFunctions\StaticFunctions;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use App\Client;
use App\TimeSheet;
use App\TimeSheetInvoices;
use App\TimeSheetInvoicesItem;
use App\TimeSheetInvoicesPayments;
use App\Http\Resources\TimeSheet\TimeSheetResource;
use App\Http\Resources\TimeSheet\PreviewTimeSheetResource;


class TimeSheetController extends Controller
{
	public function getAllTimeSheet(Request $req)
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
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
            	// $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
            	// $data['MinutesPerUnit'] = $minutesPerUnit;
            	// $data['MinutesPerUnit'] = StaticFunctions::totalUnitByCompany($minutesPerUnit);
               if($minutesPerUnit > 0) {
                  $getTimeSheet = TimeSheet::with('client.code','get_service')->get();
                  if($getTimeSheet->isNotEmpty()) {
                     $timeSheet =TimeSheetResource::collection($getTimeSheet);
                     return Response::json(['status' => 'success', 'data' => $timeSheet]);
                  } else {
                     $errors['ErrorMessage'] = ['TimeSheet Not Found !!'];
                     return Response::json(['status' => 'error', 'data' => $errors]);
                  }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

	public function addTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','client_id','job_id','work_desc','work_unit','work_date','employee_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'client_id' => 'Client ID',
         'job_id' => 'Job ID',
         'work_desc' => 'Work Description',
         'work_unit' => 'Work Unit',
         'work_date' => 'Work Date',
         'employee_id' => 'Employee ID'
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'client_id' => 'required|numeric',
         'job_id' => 'required|numeric',
         'employee_id' => 'required|numeric',
         'work_unit' => 'required|numeric',
         'work_date' => 'required|date',
         'module_id' => 'required|string',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $sum = TimeSheet::where('work_date',StaticFunctions::dateRequets($data['work_date']))->sum('work_unit');
               	if($sum<=24) {
               		$addSum = 24-$sum;
               		if($data['work_unit']<=$addSum) {
               			$addTimeSheet = TimeSheet::create([
   	            		'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id']),
   	            		'client_id'=>$data['client_id'],
   	            		'job_id'=>$data['job_id'],
   	            		'work_desc'=>$data['work_desc']?$data['work_desc']:'',
   	            		'work_unit'=>$data['work_unit'],
   	            		'work_date'=>StaticFunctions::dateRequets($data['work_date']),
   	            		'employee_id'=>$data['employee_id'],
   	            		'charge_out_rate'=>StaticFunctions::GetKeyValue($database,'minutesperunit'),
   	            		'post_status'=>TimeSheet::WIP
   		            	]);
   		            	if($addTimeSheet) {
   		            		$success['SuccessMessage'] = ['TimeSheet Add Successfully !!'];
   		            		return Response::json(['status' => 'success', 'data' => $success]);
   		            	} else {
   		               	$errors['ErrorMessage'] = ['Failed to Add ,please try agian!!'];
   		               	return Response::json(['status' => 'error', 'data' => $errors]);
   		            	}
   	            	} else {
   		               $errors['ErrorMessage'] = ['24 Units are Allowed on each Day!!'];
   		               return Response::json(['status' => 'error', 'data' => $errors]);
   		            }
   	            } else {
   	               $errors['ErrorMessage'] = ['24 Units are Allowed on each Day!!'];
   	               return Response::json(['status' => 'error', 'data' => $errors]);
   	            }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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
	
	public function editTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','sheet_id'];
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
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
               	$editTimeSheet = TimeSheet::with('client.code','get_service')->where('sheet_id',$data['sheet_id'])->first();
   					if(!empty($editTimeSheet)) {
   						$timeSheet = new TimeSheetResource($editTimeSheet);
   		            return Response::json(['status' => 'success', 'data' => $timeSheet]);
   					} else {
   	               $errors['ErrorMessage'] = ['TimeSheet Not Found !!'];
   	               return Response::json(['status' => 'error', 'data' => $errors]);
   	            }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

	public function updateTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','client_id','job_id','work_desc','work_unit','work_date','employee_id','sheet_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'client_id' => 'Client ID',
         'job_id' => 'Job ID',
         'work_desc' => 'Work Description',
         'work_unit' => 'Work Unit',
         'work_date' => 'Work Date',
         'employee_id' => 'Employee ID',
         'sheet_id' => 'Sheet ID'
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'client_id' => 'required|numeric',
         'job_id' => 'required|numeric',
         'employee_id' => 'required|numeric',
         'work_unit' => 'required|numeric',
         'sheet_id' => 'required|numeric',
         'work_date' => 'required|date',
         'module_id' => 'required|string',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $sum = TimeSheet::where('work_date',StaticFunctions::dateRequets($data['work_date']))->where('sheet_id','<>',$data['sheet_id'])->sum('work_unit');
               	if($sum<=24) {
               		$addSum = 24-$sum;
               		if($data['work_unit']<=$addSum) {
               			$updateTimeSheet = TimeSheet::where('sheet_id',$data['sheet_id'])->update([
   	            		'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id']),
   	            		'client_id'=>$data['client_id'],
   	            		'job_id'=>$data['job_id'],
   	            		'work_desc'=>!empty($data['work_desc'])?$data['work_desc']:'',
   	            		'work_unit'=>$data['work_unit'],
   	            		'work_date'=>StaticFunctions::dateRequets($data['work_date']),
   	            		'employee_id'=>$data['employee_id'],
   	            		'charge_out_rate'=>StaticFunctions::GetKeyValue($database,'minutesperunit'),
   	            		'post_status'=>TimeSheet::WIP
   		            	]);
   		            	if($updateTimeSheet) {
   		            		$success['SuccessMessage'] = ['TimeSheet Update Successfully !!'];
   		            		return Response::json(['status' => 'success', 'data' => $success]);
   		            	} else {
   		               	$errors['ErrorMessage'] = ['Failed to Update ,please try agian!!'];
   		               	return Response::json(['status' => 'error', 'data' => $errors]);
   		            	}
   	            	} else {
   		               $errors['ErrorMessage'] = ['24 Units are Allowed on each Day !!'];
   		               return Response::json(['status' => 'error', 'data' => $errors]);
   		            }
   	            } else {
   	               $errors['ErrorMessage'] = ['24 Units are Allowed on each Day!!'];
   	               return Response::json(['status' => 'error', 'data' => $errors]);
   	            }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

	public function deleteTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','sheet_id'];
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
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
               	$deleteTimeSheet = TimeSheet::where('sheet_id',$data['sheet_id'])->delete();
   					if($deleteTimeSheet) {
   						$success['SuccessMessage'] = ['TimeSheet Delete Successfully !!'];
   		            return Response::json(['status' => 'success', 'data' => $success]);
   					} else {
   	               $errors['ErrorMessage'] = ['TimeSheet Not Found !!'];
   	               return Response::json(['status' => 'error', 'data' => $errors]);
   	            }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

   public function repostTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','sheet_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'sheet_id' => 'Sheet ID'
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'sheet_id' => 'required|numeric',
         'module_id' => 'required|string',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
               	$updateTimeSheet = TimeSheet::where('sheet_id',$data['sheet_id'])->update([
   	            		'post_status'=>TimeSheet::REPOST
   		            	]);
               	if($updateTimeSheet) {
               		$success['SuccessMessage'] = ['TimeSheet RePost Update Successfully !!'];
               		return Response::json(['status' => 'success', 'data' => $success]);
               	} else {
               		$errors['ErrorMessage'] = ['Failed to Update ,please try agian!!'];
               		return Response::json(['status' => 'error', 'data' => $errors]);
               	}
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

	public function postTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','start_date','end_date'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'start_date' => 'Start Date',
         'end_date' => 'End Date '
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'start_date' => 'required|date',
         'end_date' => 'required|date',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $start = StaticFunctions::dateRequets($data['start_date']);
               	$end = StaticFunctions::dateRequets($data['end_date']);
               	$updateTimeSheet = TimeSheet::whereBetween('work_date', [$start, $end])->where('post_status','<>',TimeSheet::INVOICED)->update([
               		'post_status'=>TimeSheet::POSTED
               	]);
               	if($updateTimeSheet) {
               		$success['SuccessMessage'] = ['TimeSheet Posted Successfully !!'];
               		return Response::json(['status' => 'success', 'data' => $success]);
               	} else {
               		$errors['ErrorMessage'] = ['Failed to Update Posted ,please try agian!!'];
               		return Response::json(['status' => 'error', 'data' => $errors]);
               	}
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

	public function previewTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','start_date','end_date','client_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'start_date' => 'Start Date',
         'end_date' => 'End Date',
         'client_id' => 'Client ID'
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'start_date' => 'required|date',
         'end_date' => 'required|date',
         'client_id' => 'required|numeric',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $start = StaticFunctions::dateRequets($data['start_date']);
               	$end = StaticFunctions::dateRequets($data['end_date']);
                  $preview = TimeSheet::with('client.code','get_service')->whereBetween('work_date', [$start, $end])->where(['client_id'=>$data['client_id'],'post_status'=>TimeSheet::POSTED])->get();
               	if($preview->isNotEmpty()) {
               		$previewTimeSheet = PreviewTimeSheetResource::collection($preview);
               		return Response::json(['status' => 'success', 'data' => $previewTimeSheet]);
               	} else {
               		$errors['ErrorMessage'] = ['TimeSheet Not Found!!'];
               		return Response::json(['status' => 'error', 'data' => $errors]);
               	}
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

	public function addPreviewTimeSheet(Request $req)
	{
		$FilterRequest = ['company_id','module_id','start_date','end_date','client_id','description','discount','discount_type','net_total','grand_total','timesheet_id'];
      $data = $req->only($FilterRequest);
      // return $data['timesheet_id'];
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'start_date' => 'Start Date',
         'end_date' => 'End Date',
         'client_id' => 'Client ID',
         'description' => 'Description',
         'discount' => 'Discount',
         'discount_type' => 'Discount Type',
         'net_total' => 'Net Total',
         'grand_total' => 'Grand Total',
         'timesheet_id' => 'Timesheet ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'start_date' => 'required|date',
         'end_date' => 'required|date',
         'client_id' => 'required|numeric',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
               	$start = StaticFunctions::dateRequets($data['start_date']);
               	$end = StaticFunctions::dateRequets($data['end_date']);
               	$invoiced = TimeSheet::whereBetween('work_date', [$start, $end])->where(['client_id'=>$data['client_id'],'post_status'=>TimeSheet::POSTED])->update([
               	 	'post_status'=>TimeSheet::INVOICED
               	 ]);
                     $timeSheetInvoices = TimeSheetInvoices::create([
               			'client_id' => $data['client_id'],
   				         'description' => $data['description'],
   				         'discount' => $data['discount'],
   				         'discount_type' => $data['discount_type'],
   				         'net_total' => $data['net_total'],
   				         'grand_total' => $data['grand_total'],
   				         'invoice_datetime' => now(),
   				         'invoice_status' => TimeSheetInvoices::INVOIVENEW,
   				         'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id'])
               		]);
               		if(!empty($timeSheetInvoices)) {
               			foreach ($data['timesheet_id'] as $key => $value) {
               				TimeSheetInvoicesItem::create([
               					'timesheet_id'=>$value,
               					'invoice_id'=>$timeSheetInvoices->invoice_id,
               					'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id'])
               				]);
               			}
               			$success['SuccessMessage'] = ['Add Time Sheet Invoice Successfully!!'];
               			return Response::json(['status' => 'success', 'data' => $success]);
               		} else {
               			$errors['ErrorMessage'] = ['Failed to Add Time Sheet Invoices ,please try agian!!'];
               			return Response::json(['status' => 'error', 'data' => $errors]);
   	            	}
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

	public function timeSheetInvoicesShow(Request $req)
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
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $invoiced = TimeSheetInvoices::with('invoices_payments','client')->get();
                  if($invoiced->isNotEmpty()) {
                     foreach ($invoiced as $key => $value) {
                        $amount=0;
                        $array['InvoiceID'] = $value->invoice_id;
                        $array['ClientID'] = $value->client_id;
                        $array['ClientName'] = $value->client->client_name;
                        $array['Description'] = $value->description;
                        $array['Discount'] = $value->discount;
                        $array['DiscountType'] = $value->discount_type;
                        $array['NetTotal'] = $value->net_total;
                        $array['GrandTotal'] = $value->grand_total;
                        $array['InvoiceDatetime'] = $value->invoice_datetime;
                        $array['InvoiceStatus'] = $value->invoice_status;
                        $array['ModuleID'] = $value->module_id;
                        if(!empty($value->invoices_payments)) {
                           foreach ($value->invoices_payments as $key1 => $value1) {
                              $amount = $amount + $value1->amount_received;
                           }
                           $array['AmountReceived'] = $amount; 
                        } else {
                           $array['AmountReceived'] = $amount; 
                        }
                        $new[]=$array;
                     }
                     if(!empty($new)) {
                        return Response::json(['status' => 'success', 'data' => $new]);
                     } else {
                        $errors['ErrorMessage'] = ['Time Sheet Invoices Not Found !!'];
                        return Response::json(['status' => 'error', 'data' => $errors]);
                     }
                  } else {
                     $errors['ErrorMessage'] = ['Time Sheet Invoices Not Found !!'];
                     return Response::json(['status' => 'error', 'data' => $errors]);
                  }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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


   public function editTimesheeInvoice(Request $req)
   {
      $FilterRequest = ['company_id','module_id','invoice_id'];
      $data = $req->only($FilterRequest);
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'invoice_id' => 'Invoice ID',
  
      ];
      $validator = Validator::make($data ,[
         'module_id' => 'required|string',
         'company_id' => 'required|numeric',
         'invoice_id' => 'required|numeric',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $invoiced = TimeSheetInvoices::with('invoices_payments_select','client.code')->where('invoice_id',$data['invoice_id'])->first();
                  $array['InvoiceID'] = $invoiced->invoice_id;
                  $array['ClientID'] = $invoiced->client_id;
                  $array['ClientName'] = $invoiced->client->client_name;
                  $array['ClientCode'] = $invoiced->client->code->code_alpha."-".$invoiced->client->code->code_digit."-".$invoiced->invoice_id;
                  $array['Description'] = $invoiced->description;
                  $array['Discount'] = $invoiced->discount;
                  $array['DiscountType'] = $invoiced->discount_type;
                  $array['NetTotal'] = $invoiced->net_total;
                  $array['GrandTotal'] = $invoiced->grand_total;
                  $array['InvoiceDatetime'] = $invoiced->invoice_datetime;
                  $array['InvoiceStatus'] = $invoiced->invoice_status;
                  $array['ModuleID'] = $invoiced->module_id;
                  $array['InvoicesPayments'] = $invoiced->invoices_payments_select;

                  if(!empty($array)){
                     return Response::json(['status' => 'success', 'data' => $array]);
                  } else {
                     $errors['ErrorMessage'] = ['Time Sheet Invoices Not Found !!'];
                     return Response::json(['status' => 'error', 'data' => $errors]);
                  }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

   public function updateTimesheeInvoice(Request $req)
   {
      $FilterRequest = ['company_id','module_id','invoice_id','discount','discount_type','net_total','grand_total','description'];
      $data = $req->only($FilterRequest);
      // return $data['timesheet_id'];
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'description' => 'Description',
         'discount' => 'Discount',
         'discount_type' => 'Discount Type',
         'net_total' => 'Net Total',
         'grand_total' => 'Grand Total',
         'invoice_id' => 'Invoice ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'invoice_id' => 'required|numeric',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $updatetimeSheetInvoices = TimeSheetInvoices::where('invoice_id',$data['invoice_id'])->update([
                     'description' => $data['description'],
                     'discount' => $data['discount'],
                     'discount_type' => $data['discount_type'],
                     'net_total' => $data['net_total'],
                     'grand_total' => $data['grand_total'],
                     'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id'])
                  ]);
                  if($updatetimeSheetInvoices) {
                     $success['SuccessMessage'] = ['Update Time Sheet Invoice Successfully!!'];
                     return Response::json(['status' => 'success', 'data' => $success]);
                  } else {
                     $errors['ErrorMessage'] = ['Failed to Add Time Sheet Invoices ,please try agian!!'];
                     return Response::json(['status' => 'error', 'data' => $errors]);
                  }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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


   public function addTimesheeInvoicePayments(Request $req)
   {
      $FilterRequest = ['company_id','module_id','invoice_id','amount_received'];
      $data = $req->only($FilterRequest);
      // return $data['timesheet_id'];
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'invoice_id' => 'Invoice ID',
         'amount_received' => 'Amount Received',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'invoice_id' => 'required|numeric',
         'amount_received' => 'required|numeric',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $addTimeSheetInvoicesPayments = TimeSheetInvoicesPayments::create([
                     'invoice_id' => $data['invoice_id'],
                     'amount_received' => $data['amount_received'],
                     'received_date' => now(),
                     'module_id'=>StaticFunctions::getModuleSlugByID($data['module_id'])
                  ]);
                  if(!empty($addTimeSheetInvoicesPayments)) {
                     $success['SuccessMessage'] = ['Add Time Sheet Invoice Payments Successfully!!'];
                     return Response::json(['status' => 'success', 'data' => $success]);
                  } else {
                     $errors['ErrorMessage'] = ['Failed to Add Time Sheet Invoices ,please try agian!!'];
                     return Response::json(['status' => 'error', 'data' => $errors]);
                  }
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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

   public function clientServiceTimeSheet(Request $req)
   {
     $FilterRequest = ['company_id','module_id','client_id'];
      $data = $req->only($FilterRequest);
      // return $data['timesheet_id'];
      $attributes = [
         'company_id' => 'Company ID',
         'module_id' => 'Module ID',
         'client_id' => 'Client ID',
      ];
      $validator = Validator::make($data ,[
         'company_id' => 'required|numeric',
         'module_id' => 'required|string',
         'client_id' => 'required|numeric',
      ])->setAttributeNames($attributes);

      if($validator->fails())
      {
         return Response::json(['status' => 'error', 'data' => $validator->errors()]);
      }
      $database = CompanyConfig::where('company_id',$data['company_id'])->get();
      if($database) {
         $company_db = StaticFunctions::GetKeyValue($database,'company_database');
         $minutesPerUnit = StaticFunctions::GetKeyValue($database,'minutesperunit');
         if($company_db) {
            $db_con = StaticFunctions::db_connection(strtolower($company_db));
            if($db_con) {
               if($minutesPerUnit > 0) {
                  $getServices = Client::with('get_service')->where('client_id',$req->client_id)->first();
                  if($getServices->get_service->isNotEmpty()) {
                     foreach ($getServices->get_service as $key => $value) {
                        $array['ClientServiceID']= $value->cs_id;
                        $array['ClientServiceName']= $value->service_name;
                        $temp[]=$array;
                     }
                     return Response::json(['status' => 'success', 'data' => $temp]);
                  } else {
                     $errors['ErrorMessage'] = ['Client Service Not Found !!'];
                     return Response::json(['status' => 'error', 'data' => $errors]);
                  }
                 
               } else {
                  $errors['ErrorMessage'] = ['Your Account is Not Ready For TimeSheet Please Contact Your Company Partner'];
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
