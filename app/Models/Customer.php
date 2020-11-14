<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class Customer extends Model
{
    use Searchable, Notifiable;

    protected $fillable = [
        'uuid',
        'company_id',
        'firstname',
        'lastname',
        'phone',
        'email',
        'address'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts()
    {
        return $this->belongsToMany(ContactField::class, 'customer_contacts')->withPivot(['value']);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    /**
     * @return BelongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class)
                    ->withPivot(['is_paid', 'paid_at'])
                    ->using(CustomerOrder::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();
        $contacts = $this->contacts;
        foreach ($contacts as $contact) {
            $array['contact_' . $contact->name] = $contact->pivot->value;
        }
        $company = $this->company->toArray();
        foreach ($company as $attributeKey => $attributeValue) {
            $company['company_' . $attributeKey] = $attributeValue;
            unset($company[$attributeKey]);
        }
        return array_merge($array, $company);
    }
}
