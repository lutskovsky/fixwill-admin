<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Models\OrderType;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Exception;
use Illuminate\Support\Facades\Artisan;

/**
 * Class OrderTypeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class OrderTypeCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(OrderType::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/order-type');
        CRUD::setEntityNameStrings('тип заказа', 'типы заказа');
    }

    public function sync()
    {
        try {
            Artisan::call('order_types:sync');
            Alert::success('Types synced successfully!')->flash();
        } catch (Exception $e) {
            Alert::error('Sync failed: ' . $e->getMessage())->flash();
        }
        return redirect()->back();
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::denyAccess('delete');
        CRUD::denyAccess('create');
        CRUD::denyAccess('show');
        CRUD::addButton('top', 'sync', 'view', 'vendor.backpack.ui.inc.sync');

        CRUD::column('name')->label('Тип заказа');

        CRUD::column('operator_required')->type('check')
            ->label('Оператор обязателен');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        CRUD::field('name')
            ->label('Тип заказа')
            ->type('text')
            ->attributes(['disabled' => 'disabled']);

        CRUD::field('operator_required')
            ->label('Оператор обязателен')
            ->type('boolean')
            ->default(false);
    }
}
