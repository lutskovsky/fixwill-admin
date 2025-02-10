crud.field('accepted_by_operator').onChange(function (field) {
    if (field.value != 1) {
        crud.field('success_for_operator').check(false);
    }
}).change();

crud.field('success_for_operator').onChange(function (field) {
    if (field.value == 1) {
        crud.field('accepted_by_operator').check(true);
    }
}).change();
