<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Master data guru/karyawan (NIP + nama), imported from the Alcent payroll sheet.
 * Only employees listed here may register in the "Guru Karyawan" category.
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected $fillable = ['nip', 'name'];
}
