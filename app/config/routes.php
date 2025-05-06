<?php

use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

$userController = new app\controllers\UserController();
$router->get('/', [$userController, 'showLogin']);
$router->post('/authenticate', [$userController, 'authenticate']);
$router->get('/logout', [$userController, 'logout']);

$adminController = new app\controllers\AdminController();
$router->get('/admin', [$adminController, 'getAdmin']);
$router->post('/admin/insertDepartment', [$adminController, 'insertDepartment']);
$router->post('/admin/insertType', [$adminController, 'insertType']);
$router->post('/admin/insertExercise', [$adminController, 'insertExercise']);
$router->post('/admin/confirmTransaction', [$adminController, 'confirmTransaction']);
$router->post('/admin/updateTransactions', [$adminController, 'updateTransactions']);
$router->get('/admin/getBudgetData', [$adminController, 'getBudgetData2']);
$router->post('/admin/importDepartmentsCsv', [$adminController, 'importDepartmentsCsv']);
$router->post('/admin/importTypesCsv', [$adminController, 'importTypesCsv']);
$router->post('/admin/importExercisesCsv', [$adminController, 'importExercisesCsv']);
$router->post('/admin/importTransactionsCsv', [$adminController, 'importTransactionsCsv']);
$router->post('/admin/exportBudgetDataCsv', [$adminController, 'exportBudgetDataCsv']);
$router->get('/admin/exportBudgetDataPdf', [$adminController, 'exportBudgetDataPdf']);
$router->post('/admin/updateDepartment', [$adminController, 'updateDepartment']);
$router->post('/admin/deleteDepartment', [$adminController, 'deleteDepartment']);
$router->post('/admin/updateType', [$adminController, 'updateType']);
$router->post('/admin/deleteType', [$adminController, 'deleteType']);
$router->post('/admin/updateExercise', [$adminController, 'updateExercise']);
$router->post('/admin/deleteExercise', [$adminController, 'deleteExercise']);
$router->get('/admin/exportListsPdf', [$adminController, 'exportListsPdf']);

$clientController = new app\controllers\ClientController();
$router->get('/client', [$clientController, 'showClientPage']);
$router->post('/client/importCsv', [$clientController, 'importClientsCsv']);
$router->get('/client/exportPdf', [$clientController, 'exportClientsPdf']);

$actionClientController = new app\controllers\ActionClientController();
$router->get('/action-client', [$actionClientController, 'showActionStats']);
$router->post('/action-client/importCsv', [$actionClientController, 'importActionsEffectueesCsv']);
$router->get('/action-client/exportPdf', [$actionClientController, 'exportActionsEffectueesPdf']);
$router->post('/action-client/importActionsCsv', [$actionClientController, 'importActionsCsv']);
$router->get('/action-client/exportActionsPdf', [$actionClientController, 'exportActionsPdf']);

$reactionClientController = new app\controllers\ReactionClientController();
$router->get('/reaction-client', [$reactionClientController, 'showReactionStats']);
$router->post('/reaction-client/importCsv', [$reactionClientController, 'importReactionsEffectueesCsv']);
$router->get('/reaction-client/exportPdf', [$reactionClientController, 'exportReactionsEffectueesPdf']);
$router->post('/reaction-client/importReactionsCsv', [$reactionClientController, 'importReactionsCsv']);
$router->get('/reaction-client/exportReactionsPdf', [$reactionClientController, 'exportReactionsPdf']);
$router->post('/reaction-client/valider-reaction', [$reactionClientController, 'handleEffectuerReactionForm']);
$router->get('/reaction-client/liste-reaction-pending', [$reactionClientController, 'afficherListeReactionPending']);
$router->post('/reaction-client/accepter-reaction', [$reactionClientController, 'validerReaction']);
$router->post('/reaction-client/refuser-reaction', [$reactionClientController, 'refuserReaction']);
$router->get('/reaction-client/effectuer-reaction', [$reactionClientController, 'showEffectuerReactionForm']);

// New Routes for Type Reactions
$router->post('/reaction-client/type-reaction/save', [$reactionClientController, 'saveTypeReaction']);
$router->post('/reaction-client/type-reaction/delete', [$reactionClientController, 'deleteTypeReaction']);

// New Routes for Reactions
$router->post('/reaction-client/reaction/save', [$reactionClientController, 'saveReaction']);
$router->post('/reaction-client/reaction/delete', [$reactionClientController, 'deleteReaction']);

$reactionImpactController = new app\controllers\ReactionImpactController();
$router->get('/reaction-impact', [$reactionImpactController, 'showImpact']);


$homeController = new app\controllers\HomeController();
$router->get('/home', [$homeController, 'getHome']);
$router->post('/home/updateTransactions', [$homeController, 'updateTransactions']);
$router->post('/home/importTransactionsCsv', [$homeController, 'importTransactionsCsv']);  // Ajout du chemin pour l'importation des transactions CSV
$router->post('/home/importBudgetElementsCsv', [$homeController, 'importBudgetElementsCsv']);  // Ajout du chemin pour l'importation des éléments de budget CSV
$router->get('/getPeriods', [$homeController, 'getPeriods']);
$router->get('/home/exportTransactionsPdf', [$homeController, 'exportTransactionsPdf']);
$router->get('/home/exportTransactionsCsv', [$homeController, 'exportTransactionsCsv']);
$router->post('/home/insertBudgetElement', [$homeController, 'insertBudgetElement']);
$router->post('/home/updateBudgetElement', [$homeController, 'updateBudgetElement']);
$router->post('/home/deleteBudgetElement', [$homeController, 'deleteBudgetElement']);

$router->get('/office', function() {
    Flight::render('office');
});