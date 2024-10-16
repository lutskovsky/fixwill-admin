<?php /** @noinspection PhpParamsInspection */

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EmployeeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class EmployeeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EmployeeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Employee::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/employee');
        CRUD::setEntityNameStrings('сотрудник', 'сотрудники');

        CRUD::column([
            'name'  => 'name',
            'label' => 'Имя',
        ]);
        CRUD::column([
            'name'  => 'internal_phone',
            'label' => 'Внутренний номер',
        ]);
        CRUD::column([
            'name'  => 'remonline_login',
            'label' => 'Логин в Ремонлайне',
        ]);
        CRUD::column([
            'name'  => 'tg_login',
            'label' => 'Номер телефона аккаунта Telegram',
            'priority' => 10,
        ]);
        $this->crud->addColumn([
            'name' => 'virtual_numbers_list', // The accessor name
            'type' => 'model_function',
            'label' => 'Вирт. номера',
            'function_name' => 'getVirtualNumbersListAttribute', // The accessor method
            'priority' => 5,
        ]);

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
//        CRUD::setFromDb(); // set columns from db columns.


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
        CRUD::setValidation(EmployeeRequest::class);

        CRUD::field('name')->label('Имя');
        CRUD::field('internal_phone')->label('Внутренний номер');
        CRUD::field('remonline_login')->label('Логин в Ремонлайне');
        CRUD::field('tg_login')->label('Номер телефона аккаунта Telegram');
        $this->crud->addField([
            'label'     => "Виртуальные номера",
            'type'      => 'select_multiple',
            'name'      => 'virtualNumbers', // the method on your model that defines the relationship
            'entity'    => 'virtualNumbers', // the method on your model that defines the relationship
            'attribute' => 'number', // foreign key attribute that is shown to user
            'model'     => "App\Models\VirtualNumber", // foreign key model
            'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
            'allows_null' => true, // Optional: Allow no selection
        ]);

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
        $this->setupCreateOperation();
    }
}
