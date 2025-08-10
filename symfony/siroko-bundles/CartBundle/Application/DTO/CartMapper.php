<?php

namespace CartBundle\Application\DTO;

use CartBundle\Domain\Model\Cart;
use CartBundle\Domain\Model\CartItem;

class CartMapper
{
    public function toArray(Cart $cart): array
    {
        return [
        'cartCode' => $cart->getCode()->value(),
        'customerId' => $cart->getCustomerId()?->value(),
        'status' => $cart->getStatus(),
        'createdAt' => $cart->getCreatedAt()->format(DATE_ATOM),
        'updatedAt' => $cart->getUpdatedAt()->format(DATE_ATOM),
        'checkedOutAt' => $cart->getCheckedOutAt()?->format(DATE_ATOM),
        'items' => array_map(fn($item) => $this->mapItem($item), $cart->getItems()),
        'total' => $cart->getTotal(),
        ];
    }

    private function mapItem(CartItem $item): array
    {
        return [
            'productId' => $item->getProductId()->value(),
            'productName' => $item->getProductName()->value(),
            'productPrice' => $item->getProductPrice()->value(),
            'qty' => $item->getProductQty()->value(),
            'totalPrice' => $item->getTotalPrice(),
        ];
    }
}
