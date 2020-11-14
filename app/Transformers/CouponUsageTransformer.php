<?php

namespace App\Transformers;


use App\Dorcas\Common\APITransformerTrait;
use App\Models\CouponUsage;
use League\Fractal\TransformerAbstract;

class CouponUsageTransformer extends TransformerAbstract
{
    use APITransformerTrait;
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['coupon', 'user'];
    
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = ['coupon', 'user'];
    
    /**
     * @param CouponUsage $couponUsage
     *
     * @return array
     */
    public function transform(CouponUsage $couponUsage)
    {
        return [
            'embeds' => $this->getEmbeds(),
            'id' => $couponUsage->uuid,
            'updated_at' => !empty($couponUsage->updated_at) ? $couponUsage->updated_at->toIso8601String() : null,
            'created_at' => $couponUsage->created_at->toIso8601String()
        ];
    }
    
    /**
     * @param CouponUsage $couponUsage
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeCoupon(CouponUsage $couponUsage)
    {
        return $this->item($couponUsage->coupon, new CouponTransformer(), 'coupon');
    }
    
    /**
     * @param CouponUsage $couponUsage
     *
     * @return \League\Fractal\Resource\Item
     */
    public function includeUser(CouponUsage $couponUsage)
    {
        return $this->item($couponUsage->user, new UserTransformer(), 'user');
    }
}