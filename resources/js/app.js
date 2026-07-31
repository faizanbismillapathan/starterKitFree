import './bootstrap';

import Alpine from 'alpinejs';

import themeSwitcher from './components/theme-switcher';
import passwordField from './components/password-field';
import confirmAction from './components/confirm-action';
import fileUpload from './components/file-upload';
import dataTable from './components/data-table';
import dirtyForm from './components/dirty-form';
import toastStack from './components/toast-stack';
import chart from './components/chart';

/*
 * Alpine components are registered explicitly rather than discovered so the
 * bundle stays predictable and tree-shakeable (38_Performance_Guide.md §19).
 */
Alpine.data('themeSwitcher', themeSwitcher);
Alpine.data('passwordField', passwordField);
Alpine.data('confirmAction', confirmAction);
Alpine.data('fileUpload', fileUpload);
Alpine.data('dataTable', dataTable);
Alpine.data('dirtyForm', dirtyForm);
Alpine.data('toastStack', toastStack);
Alpine.data('chart', chart);

window.Alpine = Alpine;

Alpine.start();
