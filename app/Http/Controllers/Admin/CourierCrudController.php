<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CourierRequest;
use App\Integrations\RemonlineApi;
use App\Models\Courier;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CourierCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class CourierCrudController extends CrudController
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
        CRUD::setModel(Courier::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/courier');
        CRUD::setEntityNameStrings('курьер', 'курьеры');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addColumns([
            [
                'name' => 'name',
                'label' => 'Имя курьера в Реме',
                'type' => 'text',
            ],
            [
                'name' => 'sip_line_id',
                'type' => 'select',
                'label' => 'SIP-линия',
                'model' => "App\Models\SipLine", // related model
                'entity' => 'sipLine',
                'attribute' => 'description',
                'priority' => 10,
            ],
//            [
//                'name' => 'internal_phone',
//                'label' => 'Внутренний номер',
//            ],
            [
                'name' => 'tg_login',
                'label' => 'Номер телефона аккаунта Telegram',
                'priority' => 10,
            ],
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CourierRequest::class);
//        CRUD::setFromDb(); // set fields from db columns.

        $this->crud->addFields([

            [
                'label' => "Имя курьера в Реме",
                'type' => 'select_from_array',
                'name' => 'name',
                'allows_null' => true, // Optional: Allow no selection
                'options' => $this->getCouriers(),
            ],
            [
                'name' => 'sip_line_id',
                'type' => 'select',
                'label' => 'SIP-линия',
                'model' => "App\Models\SipLine", // related model
                'attribute' => 'description',
                'options' => (function ($query) {
                    return $query->orderBy('employee_name', 'ASC')->get();
                }),
                'priority' => 10,
            ],
//            [
//                'name' => 'internal_phone',
//                'label' => 'Внутренний номер',
//            ],
            [
                'name' => 'tg_login',
                'label' => 'Номер телефона аккаунта Telegram',
                'priority' => 10,
            ],


        ]);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    private function getCouriers()
    {
        $rem = new RemonlineApi();
        $data = $rem->getBookItems(483321)['data'];

        $output = [];
        foreach ($data as $item) {
            $output[$item['title']] = $item['title'];
        }
        return $output;
    }
}
