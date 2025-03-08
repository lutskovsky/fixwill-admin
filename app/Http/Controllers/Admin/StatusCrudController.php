<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Models\Status;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Exception;
use Illuminate\Support\Facades\Artisan;

/**
 * Class StatusCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class StatusCrudController extends CrudController
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
        CRUD::setModel(Status::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/status');
        CRUD::setEntityNameStrings('статус', 'статусы');
    }

    public function sync()
    {
        try {
            Artisan::call('statuses:sync');
            Alert::success('Statuses synced successfully!')->flash();
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
        CRUD::addButton('top', 'sync', 'view', 'vendor.backpack.crud.buttons.sync-statuses');

        CRUD::column('status_name')->label('Статус');

        CRUD::column('current')
            ->type('check')
            ->label('Текущий');
        CRUD::column('operator_required')->type('check')
            ->label('Оператор обяз.');

        CRUD::column('accepted_by_operator')
            ->type('check')
            ->label('Принят');
        CRUD::column('success_for_operator')->type('check')
            ->label('Успешный');
        CRUD::column('transit')->type('check')
            ->label('Переходный');

        CRUD::column('reschedule_before')->type('check')
            ->label('Выход перенос');
        CRUD::column('reschedule_after')->type('check')
            ->label('Вход перенос');
        CRUD::column('refusal_before')->type('check')
            ->label('Выход отказ');
        CRUD::column('refusal_after')->type('check')
            ->label('Вход отказ');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
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
        CRUD::field('status_name')
            ->label('Статус')
            ->type('text')
            ->attributes(['disabled' => 'disabled']);

        CRUD::field('operator_required')
            ->label('Оператор обязателен')
            ->type('boolean')
            ->default(false);

        CRUD::field('current')
            ->label('Текущий')
            ->type('boolean')
            ->default(false);

        CRUD::field('accepted_by_operator')
            ->label('Принят оператором')
            ->type('boolean')
            ->default(false)
            ->tab('Отчёты');

        CRUD::field('success_for_operator')
            ->label('Успешный')
            ->type('boolean')
            ->default(false)
            ->tab('Отчёты');

        CRUD::field('transit')
            ->label('Переходный')
            ->type('boolean')
            ->default(false)
            ->tab('Отчёты');

        CRUD::addFields([[   // CustomHTML
            'name' => 'header1',
            'type' => 'custom_html',
            'value' => '<h5>Переносы</h5>',
            'tab' => 'Переносы/отказы',
            'wrapper' => false
        ]]);


//        CRUD::field( 'header')
//            ->type('custom_html')
//            ->value('<h5>Переносы</h5>');

        CRUD::field('reschedule_before')
            ->label('Выход из этого статуса при переносе')
            ->type('boolean')
            ->default(false)
            ->tab('Переносы/отказы');

        CRUD::field('reschedule_after')
            ->label('Вход в этот статус при переносе')
            ->type('boolean')
            ->default(false)
            ->tab('Переносы/отказы');


        CRUD::addFields([[   // CustomHTML
            'name' => 'header2',
            'type' => 'custom_html',
            'value' => '<h5>Отказы</h5>',
            'tab' => 'Переносы/отказы',
            'wrapper' => false
        ]]);

        CRUD::field('refusal_before')
            ->label('Выход из этого статуса при отказе')
            ->type('boolean')
            ->default(false)
            ->tab('Переносы/отказы');
        CRUD::field('refusal_after')
            ->label('Вход в этот статус при отказе')
            ->type('boolean')
            ->default(false)
            ->tab('Переносы/отказы');



        Widget::add()->type('script')->content('js/backpack/status.js');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {


    }
}
