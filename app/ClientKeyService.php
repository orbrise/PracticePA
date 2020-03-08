<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientKeyService extends Model
{
    protected $table ='client_deadlines';
    protected $primaryKey = 'deadline_id';
    const DB_NAME = 'newpa';
    protected $fillable = [
    'client_id','vat_registered','vat_number','prepare_vat_return','vat_return_period','first_year','date_of_incorporation','date_of_trading','prior_accounting_reference','accounting_reference','ard','reciept_of_AA01','annual_return_date','prepare_payroll','payroll_type','payroll_start_date','first_vat_return','next_vat_return','bank_authority_date','tax_return_date','bank_letter','accounts_to_company_house','annual_return','corporation_tax_payable','corporation_tax_return','tax_partnership_return','manual_due_date','deadline_payroll','vat_return_date','unincorporated_accounts_date','tax_return_to_filled_online','tax_return_sep','other_accounts_to_company_house','other_annual_return','other_corporation_tax_payable','other_corporation_tax_return','created_at','updated_at'
    ];
    
}
