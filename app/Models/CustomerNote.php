<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class CustomerNote extends Model
{
    use Searchable;

    protected $fillable = [
        'uuid',
        'customer_id',
        'message'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();
        $customer = $this->customer->toArray();
        foreach ($customer as $attributeKey => $attributeValue) {
            $customer['customer_' . $attributeKey] = $attributeValue;
            unset($customer[$attributeKey]);
        }
        return array_merge($array, $customer);
    }
}