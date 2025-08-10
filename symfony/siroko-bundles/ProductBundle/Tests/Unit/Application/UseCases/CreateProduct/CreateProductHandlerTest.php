<?php

declare(strict_types=1);

namespace ProductBundle\Tests\Unit\Application\UseCases\CreateProduct;

use PHPUnit\Framework\TestCase;
use ProductBundle\Application\UseCases\CreateProduct\CreateProductCommand;
use ProductBundle\Application\UseCases\CreateProduct\CreateProductHandler;
use ProductBundle\Application\UseCases\CreateProduct\CreateProductValidator;
use ProductBundle\Domain\Exception\ValidationException;
use ProductBundle\Domain\Model\Product;
use ProductBundle\Domain\Repository\ProductRepository;
use ProductBundle\Domain\Event\ProductCreatedEvent;
use ProductBundle\Infrastructure\Event\DomainEventDispatcher;

class CreateProductHandlerTest extends TestCase
{
    private function createProductCommand($name='SRX M4 Oregon', $price=109.95, $stock=75): CreateProductCommand
    {
        return new CreateProductCommand($name, $price, $stock);
    }

    public function test_it_creates_a_product_successfully(): void
    {
        $command = $this->createProductCommand();

        $validator = $this->createMock(CreateProductValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($command);

        $command->id = 1;

        $repository = $this->createMock(ProductRepository::class);

        $repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Product $product) use ($command) {
                $this->assertEquals($command->name, $product->getName()->value());
                $this->assertEquals($command->price, $product->getPrice()->value());
                $this->assertEquals($command->stock, $product->getStock()->value());
                return $command->id;
            });


        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        $eventDispatcher->expects($this->once())
            ->method('dispatchAll')
            ->with($this->callback(function (array $events) use ($command) {
                return count($events) === 1 &&
                    $events[0] instanceof ProductCreatedEvent &&
                    $events[0]->productId === $command->id;
            }));

        $handler = new CreateProductHandler($repository, $eventDispatcher, $validator);

        $handler->__invoke($command);

        $this->assertTrue(true);
    }

    public function test_it_throws_an_exception_when_the_product_name_already_exists(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Name already exists\./');

        $command = $this->createProductCommand();

        $validator = $this->createMock(CreateProductValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willThrowException(new ValidationException(['Name already exists.']));

        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->never())->method('save');

        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        $eventDispatcher->expects($this->never())->method('dispatchAll');

        $handler = new CreateProductHandler($repository, $eventDispatcher, $validator);

        $handler->__invoke($command);
    }

    public function test_it_throws_an_exception_when_the_product_name_is_empty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Name is required\./');

        $command = $this->createProductCommand();
        $command->name = '';
        $validator = $this->createMock(CreateProductValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willThrowException(new ValidationException(['Name is required.']));

        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->never())->method('save');

        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        $eventDispatcher->expects($this->never())->method('dispatchAll');

        $handler = new CreateProductHandler($repository, $eventDispatcher, $validator);

        $handler->__invoke($command);
    }

    public function test_it_throws_an_exception_when_the_product_price_is_invalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Price must be greater than or equal to 0\./');

        $command = $this->createProductCommand();
        $command->price = -1;
        $validator = $this->createMock(CreateProductValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willThrowException(new ValidationException(['Price must be greater than or equal to 0.']));

        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->never())->method('save');

        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        $eventDispatcher->expects($this->never())->method('dispatchAll');

        $handler = new CreateProductHandler($repository, $eventDispatcher, $validator);

        $handler->__invoke($command);
    }

    public function test_it_throws_an_exception_when_the_product_stock_is_invalid(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/Stock must be greater than or equal to 0\./');

        $command = $this->createProductCommand();
        $command->stock = -1;
        $validator = $this->createMock(CreateProductValidator::class);
        $validator->expects($this->once())
            ->method('validate')
            ->with($command)
            ->willThrowException(new ValidationException(['Stock must be greater than or equal to 0.']));

        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->never())->method('save');

        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        $eventDispatcher->expects($this->never())->method('dispatchAll');

        $handler = new CreateProductHandler($repository, $eventDispatcher, $validator);

        $handler->__invoke($command);
    }

    public function test_it_records_a_product_created_event(): void
    {
        $command = $this->createProductCommand();
        $command->id = 7;

        $validator = $this->createMock(CreateProductValidator::class);
        $validator->expects($this->once())->method('validate')->with($command);

        $capturedProduct = null;

        $repository = $this->createMock(ProductRepository::class);
        $repository->expects($this->once())
            ->method('save')
            ->willReturnCallback(function (Product $product) use (&$capturedProduct, $command) {
                $capturedProduct = $product;
                return $command->id;
            });

        $eventDispatcher = $this->createMock(DomainEventDispatcher::class);
        $eventDispatcher->expects($this->once())->method('dispatchAll');

        $handler = new CreateProductHandler($repository, $eventDispatcher, $validator);

        $handler->__invoke($command);

        $this->assertInstanceOf(Product::class, $capturedProduct);

        $capturedProduct->setId($command->id);

        $events = $capturedProduct->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProductCreatedEvent::class, $events[0]);
        $this->assertSame($command->id, $events[0]->productId);
    }





}
