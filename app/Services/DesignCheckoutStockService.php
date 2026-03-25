<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\EnvelopeType;
use App\Models\Product;
use App\Models\SheetType;
use App\Models\Template;

class DesignCheckoutStockService
{
    /**
     * @param  array{template_id?: int|null, template_page_count?: int|null, checkout_data: array}  $context
     */
    public function assertAvailable(array $context): void
    {
        $this->checkRequirements($this->requirements($context), false);
    }

    /**
     * @param  array{template_id?: int|null, template_page_count?: int|null, checkout_data: array}  $context
     */
    public function deduct(array $context): void
    {
        $this->checkRequirements($this->requirements($context), true);
    }

    /**
     * @return array{sheet: ?array{slug: string, qty: int}, envelope: ?array{slug: string, qty: int}, product: ?array{id: int, qty: int}}
     */
    private function requirements(array $context): array
    {
        $data = $context['checkout_data'] ?? [];
        $quantity = max(1, (int) ($data['quantity'] ?? 1));

        $pageCount = (int) ($context['template_page_count'] ?? 0);
        if ($pageCount <= 0 && ! empty($context['template_id'])) {
            $pageCount = (int) (Template::query()->whereKey($context['template_id'])->value('page_count') ?? 0);
        }

        $sheetsNeeded = max(0, $pageCount * $quantity);

        $sheet = null;
        $sheetSlug = $data['sheet_type'] ?? null;
        if (is_string($sheetSlug) && $sheetSlug !== '' && $sheetsNeeded > 0) {
            $sheet = ['slug' => $sheetSlug, 'qty' => $sheetsNeeded];
        }

        $envelope = null;
        if (! empty($data['is_letter'])) {
            $envSlug = $data['envelope_cover'] ?? null;
            if (is_string($envSlug) && $envSlug !== '') {
                $envelope = ['slug' => $envSlug, 'qty' => $quantity];
            }
        }

        $product = null;
        if (! empty($data['product_id'])) {
            $product = ['id' => (int) $data['product_id'], 'qty' => $quantity];
        }

        return [
            'sheet' => $sheet,
            'envelope' => $envelope,
            'product' => $product,
        ];
    }

    /**
     * @param  array{sheet: ?array, envelope: ?array, product: ?array}  $req
     */
    private function checkRequirements(array $req, bool $mutate): void
    {
        if ($req['sheet'] !== null) {
            $slug = $req['sheet']['slug'];
            $qty = $req['sheet']['qty'];
            $model = SheetType::query()->where('slug', $slug);
            if ($mutate) {
                $model = $model->lockForUpdate();
            }
            $row = $model->first();
            if (! $row) {
                throw new InsufficientStockException('Selected sheet type is no longer available.');
            }
            if ((int) $row->stock_quantity < $qty) {
                throw new InsufficientStockException('Not enough stock for sheet type "'.$row->name.'". Please choose another option or contact us.');
            }
            if ($mutate) {
                $row->decrement('stock_quantity', $qty);
            }
        }

        if ($req['envelope'] !== null) {
            $slug = $req['envelope']['slug'];
            $qty = $req['envelope']['qty'];
            $model = EnvelopeType::query()->where('slug', $slug);
            if ($mutate) {
                $model = $model->lockForUpdate();
            }
            $row = $model->first();
            if (! $row) {
                throw new InsufficientStockException('Selected envelope type is no longer available.');
            }
            if ((int) $row->stock_quantity < $qty) {
                throw new InsufficientStockException('Not enough stock for envelope "'.$row->name.'". Please choose another option or contact us.');
            }
            if ($mutate) {
                $row->decrement('stock_quantity', $qty);
            }
        }

        if ($req['product'] !== null) {
            $id = $req['product']['id'];
            $qty = $req['product']['qty'];
            $model = Product::query()->whereKey($id);
            if ($mutate) {
                $model = $model->lockForUpdate();
            }
            $row = $model->first();
            if (! $row) {
                throw new InsufficientStockException('Selected product is no longer available.');
            }
            if ((int) $row->stock_quantity < $qty) {
                throw new InsufficientStockException('Not enough stock for product "'.$row->name.'". Please remove it or contact us.');
            }
            if ($mutate) {
                $row->decrement('stock_quantity', $qty);
            }
        }
    }
}
