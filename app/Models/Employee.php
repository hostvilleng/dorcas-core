<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class Employee extends Model
{
    use SoftDeletes, Notifiable, Searchable,Notifiable;

    protected $dates = ['hired_at'];

    protected $fillable = [
        'uuid',
        'company_id',
        'user_id',
        'department_id',
        'location_id',
        'firstname',
        'lastname',
        'gender',
        'salary_amount',
        'salary_period',
        'staff_code',
        'job_title',
        'email',
        'phone',
        'hired_at',
    ];

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->attributes['firstname'] . ' ' . $this->attributes['lastname'];
    }

    /**
     * Returns the photo URL for this model
     *
     * @return string
     */
    public function getPhotoAttribute(): string
    {
        return gravatar($this->attributes['email'] ?? 'id@example.org');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function PayrollGroup(){
        return $this->belongsToMany(PayrollPaygroup::class)->withTimestamps();
    }

    public function PayrollTransaction(){
        return $this->hasMany(PayrollTransactions::class);
    }

    public function runs(){
        return $this->belongsToMany(PayrollRun::class,'payroll_run_histories','run_id','employee_id')->withTimestamps();
    }

    public function authorizers(){
        return $this->hasMany(ApprovalAuthorizers::class,'employee_id');
    }

    public function leaveRequests(){
        return $this->hasMany(LeaveRequests::class);
    }



}