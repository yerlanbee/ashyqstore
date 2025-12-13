<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Dto\OrderDto;
use App\Domain\Order\Enums\OrderStatus;
use App\Infrastructure\Models\Contracts\OrderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $status
 * @property float $total_price
 *
 * @property-read Guest $guest
 */
class Order extends Model implements OrderContract
{
    protected $table = self::TABLE;

    protected $fillable = self::FIELDS;

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['quantity'])
            ->withTimestamps();
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public static function calculateTotalPrice(OrderDto $dto): int
    {
        $products = Product::getByIds($dto->getProducts())->keyBy('id');

        return collect($dto->items)->sum(function ($item) use ($products) {
            $product = $products[$item['product_id']];
            return $product->price * $item['quantity'];
        });
    }

    public static function findById(int $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function setPending(): void
    {
        $this->{self::FIELD_STATUS} = OrderStatus::PENDING->value;
    }

    public function setDeleted(): void
    {
        $this->{self::FIELD_STATUS} = OrderStatus::DELETED->value;
    }

    public function isDeleted(): bool
    {
        return $this->{self::FIELD_STATUS} == OrderStatus::DELETED->value;
    }

    public function getProducts(): Collection
    {
        return $this->products()->withPivot('quantity')->get()->map(function (Product $item) {
            return [
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->pivot->quantity,
            ];
        });
    }
}
