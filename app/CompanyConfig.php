<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyConfig extends Model
{
    protected $table = 'company_config';
    protected $fillable = [
        'config_name','config_value', 'created_at', 'updated_at','multi_value','company_id',
    ];

    public static  function company_data($id)
    {
        $company_data = CompanyConfig::where(['company_id'=>$id])->get();
        foreach ($company_data as $key => $value)
        {
            if($value->config_name == 'minutesperunit')
            {
                return $minutesperunit =$value->config_value;
            }
        }
    }

    public static function setupDatabase()
    {
        $queries[] = 'CREATE TABLE IF NOT EXISTS `client` (
                     `client_id` int(11) NOT NULL,
                     `company_id` int(11) DEFAULT NULL COMMENT \'from tbl_company\',
                     `client_name` varchar(70) DEFAULT NULL,
                     `trade_id` int(11) DEFAULT NULL,
                     `user_id` int(11) DEFAULT NULL COMMENT \'(partner)user_id from tbl_user\',
                     `manager_id` int(11) DEFAULT NULL,
                     `staff_id` int(11) DEFAULT NULL,
                     `payroll_id` int(11) DEFAULT NULL,
                     `registration_no` varchar(100) DEFAULT NULL,
                     `status` enum(\'Active\',\'Prospective\',\'Ceased\') NOT NULL DEFAULT \'Active\',
                     `client_acquired` date DEFAULT NULL,
                     `utr` text,
                     `paye_ref` text,
                     `paye_account_office_ref` text,
                     `vat` text,
                     `prepare_letter` int(11) DEFAULT NULL,
                     `client_type` varchar(60) DEFAULT NULL,
                     `service_type` int(11) DEFAULT NULL,
                     `company_auth_code` text COMMENT \'new colunm added.\',
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB DEFAULT  CHARSET=utf8 COLLATE=\'utf8_general_ci\' ';
        $queries[] = 'ALTER TABLE `client`  ADD PRIMARY KEY (`client_id`);';
        $queries[] = 'ALTER TABLE `client` MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';

        $queries[] = 'CREATE TABLE IF NOT EXISTS `client_codes` (
                     `id` int(11) NOT NULL,
                     `code_alpha` char(1) NOT NULL,
                     `code_digit` varchar(10) NOT NULL,
                     `client_id` int(11) NOT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';
        $queries[] = 'ALTER TABLE `client_codes`  ADD PRIMARY KEY (`id`);';
        $queries[] = 'ALTER TABLE `client_codes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';


        $queries[] = 'CREATE TABLE IF NOT EXISTS `client_contact` (
                     `contact_id` int(11) NOT NULL,
                     `company_id` int(11) DEFAULT NULL,
                     `contact_type` enum(\'company\',\'client\') DEFAULT NULL,
                     `client_id` int(11) DEFAULT NULL,
                     `contact_title` enum(\'Mr\',\'Mrs\',\'Ms\',\'Dr\',\'Other\') DEFAULT NULL,
                     `contact_other_title` varchar(50) DEFAULT NULL,
                     `first_name` varchar(250) DEFAULT NULL,
                     `last_name` varchar(250) NOT NULL,
                     `contact_designation` varchar(50) DEFAULT NULL,
                     `contact_phone_no` varchar(30) DEFAULT NULL,
                     `contact_email` varchar(30) DEFAULT NULL,
                     `contact_address_line1` text,
                     `contact_city` varchar(30) DEFAULT NULL,
                     `contact_county` varchar(255) DEFAULT NULL,
                     `contact_country` varchar(50) DEFAULT NULL,
                     `contact_postal_code` varchar(30) DEFAULT NULL,
                     `notes` text,
                     `status` varchar(20) DEFAULT NULL,
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';
        $queries[] = 'ALTER TABLE `client_contact`  ADD PRIMARY KEY (`contact_id`);';
        $queries[] = 'ALTER TABLE `client_contact` MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';


        $queries[] ='CREATE TABLE IF NOT EXISTS `client_contact_info` (
                     `cci_id` int(11) NOT NULL,
                     `client_id` int(11) DEFAULT NULL,
                     `address_type` enum(\'Registered Office\',\'Correspondence\',\'Other\') DEFAULT NULL,
                     `address_type_other` varchar(255) DEFAULT NULL,
                     `address_line1` text,
                     `address_line2` text,
                     `city` varchar(100) DEFAULT NULL,
                     `county` varchar(100) DEFAULT NULL,
                     `postal_code` varchar(50) DEFAULT NULL,
                     `country` varchar(70) DEFAULT NULL,
                     `mobile` varchar(30) DEFAULT NULL,
                     `phone_no` varchar(30) DEFAULT NULL,
                     `fax` varchar(30) DEFAULT NULL,
                     `email` varchar(30) DEFAULT NULL,
                     `website` text,
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';
        $queries[] = 'ALTER TABLE `client_contact_info`  ADD PRIMARY KEY (`cci_id`);';
        $queries[] = 'ALTER TABLE `client_contact_info` MODIFY `cci_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';

        $queries[] = 'CREATE TABLE IF NOT EXISTS `client_deadlines` (    
                     `deadline_id` int(11) NOT NULL,
                     `client_id` int(11) DEFAULT NULL COMMENT \'from client table\',
                     `vat_registered` tinyint(4) DEFAULT NULL COMMENT \'checkbox\',
                     `vat_number` text,
                     `prepare_vat_return` tinyint(4) DEFAULT NULL COMMENT \'checkbox\',
                     `vat_return_period` varchar(10) DEFAULT NULL COMMENT \'quarterly, annual\',
                     `first_year` tinyint(4) DEFAULT NULL COMMENT \'check box\',
                     `date_of_incorporation` date DEFAULT NULL,
                     `date_of_trading` date DEFAULT NULL,
                     `prior_accounting_reference` date DEFAULT NULL,
                     `accounting_reference` date DEFAULT NULL,
                     `ard` varchar(10) DEFAULT NULL COMMENT \'extended,shortened\',
                     `reciept_of_AA01` date DEFAULT NULL,
                     `annual_return_date` date DEFAULT NULL,
                     `prepare_payroll` INT NOT NULL DEFAULT \'0\',
                     `payroll_type` varchar(70) DEFAULT NULL,
                     `payroll_start_date` date DEFAULT NULL,
                     `first_vat_return` date DEFAULT NULL,
                     `next_vat_return` date DEFAULT NULL,
                     `bank_authority_date` date DEFAULT NULL,
                     `tax_return_date` date DEFAULT NULL,
                     `bank_letter` date DEFAULT NULL,
                     `accounts_to_company_house` date DEFAULT NULL,
                     `annual_return` date DEFAULT NULL,
                     `corporation_tax_payable` date DEFAULT NULL,
                     `corporation_tax_return` date DEFAULT NULL,
                     `tax_partnership_return` date DEFAULT NULL,
                     `manual_due_date` date DEFAULT NULL,
                     `deadline_payroll` date DEFAULT NULL,
                     `vat_return_date` date DEFAULT NULL,
                     `unincorporated_accounts_date` date DEFAULT NULL,
                     `tax_return_to_filled_online` tinyint(4) DEFAULT NULL COMMENT \'check box\',
                     `tax_return_sep` tinyint(4) DEFAULT NULL COMMENT \'check box\',
                     `other_accounts_to_company_house` date DEFAULT NULL COMMENT \'client type is other\',
                     `other_annual_return` date DEFAULT NULL COMMENT \'client type is other\',
                     `other_corporation_tax_payable` date DEFAULT NULL COMMENT \'client type is other\',
                     `other_corporation_tax_return` date DEFAULT NULL COMMENT \'client type is other\',
                     `created_at` datetime DEFAULT NULL,
                     `updated_at` datetime DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';
        $queries[] = 'ALTER TABLE `client_deadlines`  ADD PRIMARY KEY (`deadline_id`);';
        $queries[] = 'ALTER TABLE `client_deadlines` MODIFY `deadline_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';

        /*** client_services*******************/
        $queries[] = 'CREATE TABLE IF NOT EXISTS `client_services` (
                     `cs_id` int(11) NOT NULL,
                     `client_id` int(11) DEFAULT NULL,
                     `service_name` varchar(255) NOT NULL COMMENT \'service_id from services\',
                     `initial_date` date DEFAULT NULL,
                     `service_id` int(11) NOT NULL,
                     `duration_month` int(11) DEFAULT \'0\',
                     `duration_day` int(11) DEFAULT \'0\',
                     `service_type` enum(\'Primary\',\'Optional\') DEFAULT NULL,
                     `service_track` enum(\'Tracked\',\'Un-Tracked\') NOT NULL DEFAULT \'Tracked\',
                     `service_status` enum(\'Active\',\'Inactive\') NOT NULL DEFAULT \'Active\',
                     `repeat_type` enum(\'Years\',\'Months\',\'Weeks\',\'Days\') NOT NULL DEFAULT \'Years\',
                     `repeat_number` int(11) DEFAULT \'1\',
                     `require_confirm` int(11) DEFAULT \'0\',
                     `created_at` datetime DEFAULT NULL,
                     `updated_at` datetime DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_services`  ADD PRIMARY KEY (`cs_id`)';
        $queries[] = 'ALTER TABLE `client_services` MODIFY `cs_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End client_services*******************/

        /***client_jobs*******************/
        $queries[] = 'CREATE TABLE IF NOT EXISTS `client_jobs` (
                     `job_id` int(11) NOT NULL,
                     `cs_id` int(11) DEFAULT NULL,
                     `start_date` date DEFAULT NULL,
                     `year_end` date NOT NULL,
                     `due_date` date DEFAULT NULL,
                     `assigned_to` int(11) DEFAULT NULL COMMENT \'Staff id\',
                     `assigned_date` date DEFAULT NULL,
                     `assigned_due_date` date DEFAULT NULL COMMENT \'Due Date for Assigned Member\',
                     `job_status` enum(\'New\',\'Assigned\',\'Submitted\',\'Completed\',\'Delayed\',\'Expired\',\'Cancelled\') DEFAULT NULL,
                     `completed_by` int(11) DEFAULT NULL,
                     `completed_on` datetime DEFAULT NULL,
                     `confirmed_on` datetime DEFAULT NULL,
                     `comments` text,                    
                     `user_id` int(11) DEFAULT NULL,
                     `client_id` int(11) NOT NULL,
                     `can_contact_client` int(11) NOT NULL DEFAULT \'0\',
                     `is_documents_received` int(11) NOT NULL DEFAULT \'0\',
                     `last_checked` datetime DEFAULT NULL,    
                     `created_at` datetime DEFAULT NULL,    
                     `updated_at` datetime DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_jobs`  ADD PRIMARY KEY (`job_id`)';
        $queries[] = 'ALTER TABLE `client_jobs` MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End client_jobs*******************/

        /***Banks*******************/
        $queries[] = 'CREATE TABLE `client_bank` (
                     `bank_id` int(11) NOT NULL,
                     `client_id` int(11) DEFAULT NULL,
                     `bank_name` varchar(30) DEFAULT NULL,
                     `bank_branch` varchar(30) DEFAULT NULL,
                     `bank_manager` varchar(30) DEFAULT NULL,
                     `bank_email` varchar(50) DEFAULT NULL,
                     `bank_mobile` varchar(30) DEFAULT NULL,
                     `bank_phone` varchar(30) DEFAULT NULL,
                     `bank_address` text,
                     `bank_city` varchar(30) DEFAULT NULL,
                     `bank_county` varchar(50) DEFAULT NULL,
                     `bank_post_code` varchar(30) DEFAULT NULL,
                     `bank_country` varchar(50) DEFAULT NULL,
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_bank` ADD PRIMARY KEY (`bank_id`)';
        $queries[] = 'ALTER TABLE `client_bank` MODIFY `bank_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Banks*******************/

        /***Client Officers*******************/
        $queries[] = 'CREATE TABLE `client_officers` (
                     `contact_id` int(11) NOT NULL,
                     `company_id` int(11) DEFAULT NULL,
                     `contact_type` enum(\'company\',\'client\') DEFAULT NULL,
                     `officer_type` enum(\'Active\',\'Resigned\',\'Significant\',\'Other\') DEFAULT NULL,                    
                     `ceased_on` datetime DEFAULT NULL, 
                     `date_of_birth` varchar(50) DEFAULT NULL,
                     `appointed_on` datetime DEFAULT NULL, 
                     `resigned_on` datetime DEFAULT NULL, 
                     `client_id` int(11) DEFAULT NULL,
                     `contact_title` enum(\'Mr\',\'Mrs\',\'Ms\',\'Dr\',\'Other\') DEFAULT NULL,
                     `contact_other_title` varchar(50) DEFAULT NULL,
                     `first_name` varchar(50) DEFAULT NULL,
                     `last_name` varchar(50) DEFAULT NULL,
                     `contact_designation` varchar(50) DEFAULT NULL,
                     `contact_phone_no` varchar(30) DEFAULT NULL,
                     `contact_email` varchar(30) DEFAULT NULL,
                     `contact_address_line1` text,
                     `contact_city` varchar(30) DEFAULT NULL,
                     `contact_county` varchar(255) DEFAULT NULL,
                     `contact_country` varchar(50) DEFAULT NULL,
                     `nationality` varchar(50) DEFAULT NULL,
                     `contact_postal_code` varchar(30) DEFAULT NULL,
                     `notes` text,
                     `status` varchar(20) DEFAULT NULL,
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_officers` ADD PRIMARY KEY (`contact_id`)';
        $queries[] = 'ALTER TABLE `client_officers` MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***Client Officers*******************/

        /***Client Share*******************/
        $queries[] = 'CREATE TABLE `client_share` (
                     `share_id` int(11) NOT NULL,
                     `client_id` int(11) DEFAULT NULL,
                     `share_name` varchar(100) DEFAULT NULL,
                     `share_designation` varchar(70) DEFAULT NULL,
                     `share_type` enum(\'Ordinary\',\'Preference\') DEFAULT NULL,
                     `share_type_letter` char(1) DEFAULT NULL COMMENT \'A,B,C.....\',
                     `no_of_shares` int(11) DEFAULT NULL,
                     `share_status` enum(\'Transferred\',\'Disposed\') DEFAULT NULL,
                     `phone` varchar(100) DEFAULT NULL,
                     `mobile` varchar(100) DEFAULT NULL,
                     `email` varchar(100) DEFAULT NULL,
                     `address` text,
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_share` ADD PRIMARY KEY (`share_id`)';
        $queries[] = 'ALTER TABLE `client_share` MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Client Share*******************/

        /***Client Notes*******************/
        $queries[] = 'CREATE TABLE `client_notes` (
                     `note_id` int(11) NOT NULL,
                     `client_id` int(11) DEFAULT NULL,
                     `note_date` date DEFAULT NULL,
                     `note_time` time DEFAULT NULL,
                     `telephone_conversation` tinyint(4) DEFAULT NULL COMMENT \'1 or 0 for check box\',
                     `user_id` int(11) DEFAULT NULL,
                     `service_id` int(11) DEFAULT NULL COMMENT \'from service table\',
                     `due_date` date DEFAULT NULL,
                     `note_data` text,
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_notes` ADD PRIMARY KEY (`note_id`)';
        $queries[] = 'ALTER TABLE `client_notes` MODIFY `note_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Client Notes*******************/

        /*** Client Documents*******************/
        $queries[] = 'CREATE TABLE `client_documents` (
                     `document_id` int(11) NOT NULL,
                     `client_id` int(11) DEFAULT NULL,
                     `doc_name` varchar(255) NOT NULL,
                     `doc_description` varchar(255) NOT NULL,
                     `date_in` date DEFAULT NULL,
                     `date_out` date DEFAULT NULL,
                     `user_id` int(11) DEFAULT NULL,
                     `created_at` timestamp NULL DEFAULT NULL,
                     `updated_at` timestamp NULL DEFAULT NULL,
                     `module_id` int(11) DEFAULT NULL
                  ) ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_documents` ADD PRIMARY KEY (`document_id`)';
        $queries[] = 'ALTER TABLE `client_documents` MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Client Documents*******************/

        /*** Client Extra*******************/
        $queries[] = 'CREATE TABLE `client_extra` (
                  `extra_id` int(11) NOT NULL,
                  `client_id` int(11) DEFAULT NULL,
                  `proof_of_identity` tinyint(4) DEFAULT NULL,
                  `proof_of_address` tinyint(4) DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `updated_at` timestamp NULL DEFAULT NULL,
                  `module_id` int(11) DEFAULT NULL
                ) ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_extra` ADD PRIMARY KEY (`extra_id`)';
        $queries[] = 'ALTER TABLE `client_extra` MODIFY `extra_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Client Extra*******************/

        /*** Client Trades*******************/
        $queries[] = 'CREATE TABLE `client_trades` (
                                `id` int(11) NOT NULL,
                                `client_id` int(11) NOT NULL,
                                `trade_id` int(11) NOT NULL,
                                `created_at` datetime DEFAULT NULL,
                                `updated_at` datetime DEFAULT NULL,
                                `module_id` int(11) DEFAULT NULL
                              ) ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `client_trades` ADD PRIMARY KEY (`id`)';
        $queries[] = 'ALTER TABLE `client_trades` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Client Trades*******************/


        /*** Client NOtifications*******************/
        $queries[] = 'CREATE TABLE `client_notifications` (
                              `noti_id` int(11) NOT NULL,
                              `noti_type` enum(\'desktop\',\'email\',\'sms\',\'push\') NOT NULL DEFAULT \'desktop\',
                              `noti_title` varchar(255) NOT NULL,
                              `noti_desc` text NOT NULL,
                              `client_id` int(11) NOT NULL COMMENT \'User ID to whom this notification has been assigned, 0 = all\',
                              `status` int(1) NOT NULL DEFAULT \'0\' COMMENT \'0 = New, above 0 means read\',
                              `created_at` datetime DEFAULT NULL,
                              `updated_at` datetime DEFAULT NULL,
                              `module_id` int(11) DEFAULT NULL
                            ) ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = "ALTER TABLE `client_notifications` ADD PRIMARY KEY (`noti_id`)";
        $queries[] = 'ALTER TABLE `client_notifications` MODIFY `noti_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Client Trades*******************/


        /*** Timesheet *******************/
        $queries[] = 'CREATE TABLE `timesheet` (
                  `sheet_id` int(11) NOT NULL,
                  `employee_id` int(11) NOT NULL,
                  `client_id` int(11) NOT NULL,
                  `job_id` int(11) NOT NULL DEFAULT \'0\' COMMENT \'Optional\',
                  `work_date` date NOT NULL,
                  `work_desc` varchar(250) DEFAULT NULL,
                  `work_unit` int(11) NOT NULL,
                  `work_unit_minutes` int(11) NOT NULL DEFAULT \'0\' COMMENT \'How many minutes per Unit company config\',
                  `charge_out_rate` int(11) NOT NULL,
                  `time_start` time DEFAULT NULL,
                  `time_end` time DEFAULT NULL,
                  `work_type` int(11) DEFAULT NULL,
                  `post_status` enum(\'WIP\',\'Repost\',\'Posted\',\'Invoiced\') NOT NULL DEFAULT \'WIP\',
                  `post_date` datetime DEFAULT NULL,
                  `repost_allowed_by` int(11) DEFAULT NULL,
                  `repost_datetime` datetime DEFAULT NULL,
                  `created_at` datetime DEFAULT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  `module_id` int(11) DEFAULT NULL
                ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `timesheet` ADD PRIMARY KEY (`sheet_id`)';
        $queries[] = 'ALTER TABLE `timesheet` MODIFY `sheet_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End Timesheet*******************/

        /*** timesheet_invoices *******************/
        $queries[] = 'CREATE TABLE `timesheet_invoices` (
                    `invoice_id` int(11) NOT NULL,
                    `client_id` int(11) NOT NULL,
                    `description` text,
                    `discount` decimal(10,0) NOT NULL DEFAULT \'0\',
                    `discount_type` enum(\'Fixed\',\'Percent\') NOT NULL,
                    `net_total` double DEFAULT \'0\',
                    `grand_total` double DEFAULT \'0\',    
                    `invoice_datetime` datetime NOT NULL,
                    `invoice_due_date` datetime DEFAULT NULL,
                    `invoice_status` enum(\'New\',\'Pending\',\'Complete\') NOT NULL,
                    `created_at` datetime DEFAULT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    `module_id` int(11) DEFAULT NULL
                ) ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `timesheet_invoices` ADD PRIMARY KEY (`invoice_id`)';
        $queries[] = 'ALTER TABLE `timesheet_invoices` MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End timesheet_invoices*******************/

        /*** timesheet_invoice_items *******************/
        $queries[] = 'CREATE TABLE `timesheet_invoice_items` (
                  `ti_item_id` int(11) NOT NULL,
                  `timesheet_id` int(11) NOT NULL,
                  `invoice_id` int(11) NOT NULL,
                  `created_at` datetime DEFAULT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  `module_id` int(11) DEFAULT NULL
                ) ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `timesheet_invoice_items` ADD PRIMARY KEY (`ti_item_id`)';
        $queries[] = 'ALTER TABLE `timesheet_invoice_items` MODIFY `ti_item_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End timesheet_invoice_items*******************/

        /*** timesheet_invoice_items *******************/
        $queries[] = 'CREATE TABLE `timesheet_invoice_payments` (
                  `id` int(11) NOT NULL,
                  `amount_received` double NOT NULL,
                  `received_date` date NOT NULL,
                  `invoice_id` int(11) NOT NULL,
                  `created_at` datetime DEFAULT NULL,
                  `updated_at` datetime DEFAULT NULL,
                  `module_id` int(11) DEFAULT NULL
                )  ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `timesheet_invoice_payments` ADD PRIMARY KEY (`id`)';
        $queries[] = 'ALTER TABLE `timesheet_invoice_payments` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End timesheet_invoice_items*******************/

        /*** Organization Table *******************/
        $queries[] = 'CREATE TABLE `organizations` (
                    `id` int(11) NOT NULL,
                    `org_name` varchar(255) DEFAULT NULL,
                    `title` enum(\'Mr\',\'Mrs\',\'Ms\',\'Dr\') DEFAULT NULL,
                    `first_name` varchar(255) NOT NULL,
                    `last_name` varchar(255) NOT NULL,
                    `designation` varchar(50) DEFAULT NULL,
                    `phone` varchar(30) DEFAULT NULL,
                    `email` varchar(30) DEFAULT NULL,
                    `address` text,
                    `city` varchar(30) DEFAULT NULL,
                    `county` varchar(255) DEFAULT NULL,
                    `country` varchar(50) DEFAULT NULL,
                    `postal_code` varchar(30) DEFAULT NULL,
                    `company_id` int(11) DEFAULT NULL,
                    `notes` text,
                    `status` varchar(20) DEFAULT NULL,
                    `created_at` datetime DEFAULT NULL,
                    `updated_at` datetime DEFAULT NULL,
                    `module_id` int(11) DEFAULT NULL
                  )ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `organizations` ADD PRIMARY KEY (`id`)';
        $queries[] = 'ALTER TABLE `organizations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';
        /***End timesheet_invoice_items*******************/

        /*** KYC Onfido Table *******************/
        $queries[] = 'CREATE TABLE `kyc_onfido` (
                `id` int(11) NOT NULL,
                `reference_number` varchar(255) NOT NULL,
                `onfido_applicant_id` varchar(255) NOT NULL,
                `onfido_check_id` varchar(255) NOT NULL,
                `onfido_check_status` varchar(255) DEFAULT NULL,
                `onfido_check_result` varchar(255) NOT NULL,
                `onfido_report_id` varchar(255) NOT NULL,
                `user_id` int(11) NOT NULL,
                `user_type` enum(\'Officer\',\'Contact\',\'Staff\') NOT NULL DEFAULT \'Officer\',
                `kyc_status` enum(\'Applied\',\'Approved\',\'Rejected\',\'Withdrawn\') NOT NULL,
                `client_id` int(11) NOT NULL,
                `apply_date` datetime DEFAULT NULL,
                `update_date` datetime DEFAULT NULL,
                `module_id` int(11) DEFAULT NULL,
				`created_at` datetime DEFAULT NULL,
                `updated_at` datetime DEFAULT NULL
                )ENGINE=InnoDB   DEFAULT CHARSET=utf8 COLLATE=\'utf8_general_ci\'';

        $queries[] = 'ALTER TABLE `kyc_onfido` ADD PRIMARY KEY (`id`)';
        $queries[] = 'ALTER TABLE `kyc_onfido` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;';

        return $queries;

    }
}
