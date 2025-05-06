<?php

namespace app\controllers;

use Flight;
use FPDF;

class AdminController
{

    public function __construct() {}

    public function getAdmin()
    {
        if (!isset($_SESSION['department_id'])) {
            Flight::redirect('/');
            exit;
        }
        $generaliserModel = Flight::generaliserModel();
        $categories = $generaliserModel->getTableData('category', []);
        $join = [
            ['budget_element', [['transaction.budget_element_id', 'budget_element.budget_element_id']]],
            ['category', [['budget_element.category_id', 'category.category_id']]],
            ['exercise', [['transaction.exercise_id', 'exercise.exercise_id']]],
            ['priority', [['transaction.priority_id', 'priority.priority_id']]],
        ];
        $transactions = $generaliserModel->getTableData('transaction', ['status' => 0, 'budget_element_is_deleted' => 0, 'exercise_is_deleted' => 0], [], $join);
        $exercises = $generaliserModel->getTableData('exercise', ['exercise_is_deleted' => 0]);
        $departments = $generaliserModel->getTableData('department', ['department_is_deleted' => 0]);
        $types = $generaliserModel->getTableData('type', ['type_is_deleted' => 0]);
        $selectedExercise = Flight::request()->query->exercise ?? null;
        $budgetData = $selectedExercise ? $this->getBudgetData($selectedExercise) : [];
        Flight::render('template', [
            'pageName' => 'admin',
            'pageTitle' => 'Admin Page',
            'categories' => $categories,
            'transactions' => $transactions,
            'exercises' => $exercises,
            'types' => $types,
            'departments' => $departments,
            'selectedExercise' => $selectedExercise,
            'budgetData' => $budgetData,
            'message' => Flight::get('message')
        ]);
    }

    public function insertDepartment()
    {
        $name = Flight::request()->data->name;
        $generaliserModel = Flight::generaliserModel();
        $result = $generaliserModel->insererDonnee('department', ['name' => $name]);
        if ($result['status'] === 'success') {
            $departmentId = $generaliserModel->getLastInsertedId('department', 'department_id')['last_id'];
            $userData = [
                'name' => $name,
                'password' => $name,
                'department_id' => $departmentId,
            ];
            $generaliserModel->insererDonnee('user', $userData);
            Flight::json(['success' => true, 'message' => 'Department and user created successfully.']);
        } else {
            Flight::json(['success' => false, 'message' => 'Error creating department: ' . $result['message']]);
        }
    }

    public function updateDepartment()
    {
        $id = Flight::request()->data->id;
        $name = Flight::request()->data->name;
        Flight::generaliserModel()->updateTableData('department', ['name' => $name], ['department_id' => $id]);
        Flight::json(['success' => true, 'message' => 'Department updated successfully.']);
    }

    public function deleteDepartment()
    {
        $id = Flight::request()->data->id;
        Flight::generaliserModel()->updateTableData('department', ['department_is_deleted' => 1], ['department_id' => $id]);
        Flight::json(['success' => true, 'message' => 'Department deleted successfully.']);
    }

    public function insertType()
    {
        $name = Flight::request()->data->name;
        Flight::generaliserModel()->insererDonnee('type', ['name' => $name]);
        Flight::json(['success' => true, 'message' => 'Type created successfully.']);
    }

    public function updateType()
    {
        $id = Flight::request()->data->id;
        $name = Flight::request()->data->name;
        Flight::generaliserModel()->updateTableData('type', ['name' => $name], ['type_id' => $id]);
        Flight::json(['success' => true, 'message' => 'Type updated successfully.']);
    }

    public function deleteType()
    {
        $id = Flight::request()->data->id;
        Flight::generaliserModel()->updateTableData('type', ['type_is_deleted' => 1], ['type_id' => $id]);
        Flight::json(['success' => true, 'message' => 'Type deleted successfully.']);
    }

    public function getBudgetData2()
    {
        $exerciseId = Flight::request()->query->exercise;
        $budgetData = $this->getBudgetData($exerciseId);
        $budgetData['selectedExercise'] = $exerciseId;
        Flight::json($budgetData);
    }

    public function insertExercise()
    {
        $generaliserModel = Flight::generaliserModel();
        $startDate = Flight::request()->data->start_date;
        $nbPeriod = Flight::request()->data->nb_period;
        $startBalance = Flight::request()->data->start_balance;
        $data = [
            'start_date' => $startDate,
            'nb_period' => $nbPeriod,
            'start_balance' => $startBalance
        ];
        $result = $generaliserModel->insererDonnee('exercise', $data);
        $last_id = $generaliserModel->getLastInsertedId('exercise', 'exercise_id');
        if ($result['status'] == 'success') {
            $exerciseId = $last_id['last_id'];
            $budgetElements = $generaliserModel->getTableData('budget_element', ['budget_element_is_deleted' => 0]);
            $transactions = [];
            foreach ($budgetElements as $element) {
                for ($i = 1; $i <= $nbPeriod; $i++) {
                    $transactions[] = [
                        'budget_element_id' => $element['budget_element_id'],
                        'exercise_id' => $exerciseId,
                        'period_num' => $i,
                        'nature' => 1,
                        'amount' => 0,
                        'status' => 1,
                        'priority_id' => 1
                    ];
                    $transactions[] = [
                        'budget_element_id' => $element['budget_element_id'],
                        'exercise_id' => $exerciseId,
                        'period_num' => $i,
                        'nature' => 2,
                        'amount' => 0,
                        'status' => 1,
                        'priority_id' => 1
                    ];
                }
            }
            if (!empty($transactions)) {
                $generaliserModel->insererDonnees('transaction', $transactions);
            }
            Flight::json(['success' => true, 'message' => 'Exercise and transactions created successfully.']);
        } else {
            Flight::json(['success' => false, 'message' => 'Error creating exercise: ' . $result['message']]);
        }
    }

    public function updateExercise()
    {
        $id = Flight::request()->data->id;
        $start_date = Flight::request()->data->start_date;
        $nb_period = Flight::request()->data->nb_period;
        $start_balance = Flight::request()->data->start_balance;
        $generaliserModel = Flight::generaliserModel();
        $currentExercise = $generaliserModel->getTableData('exercise', ['exercise_id' => $id])[0];
        $currentNbPeriod = $currentExercise['nb_period'];
        $generaliserModel->updateTableData('exercise', [
            'start_date' => $start_date,
            'nb_period' => $nb_period,
            'start_balance' => $start_balance
        ], ['exercise_id' => $id]);
        if ($nb_period > $currentNbPeriod) {
            $budgetElements = $generaliserModel->getTableData('budget_element', ['budget_element_is_deleted' => 0]);
            $transactions = [];
            for ($i = $currentNbPeriod + 1; $i <= $nb_period; $i++) {
                foreach ($budgetElements as $element) {
                    $transactions[] = [
                        'budget_element_id' => $element['budget_element_id'],
                        'exercise_id' => $id,
                        'period_num' => $i,
                        'nature' => 1,
                        'amount' => 0,
                        'status' => 1,
                        'priority_id' => 1
                    ];
                    $transactions[] = [
                        'budget_element_id' => $element['budget_element_id'],
                        'exercise_id' => $id,
                        'period_num' => $i,
                        'nature' => 2,
                        'amount' => 0,
                        'status' => 1,
                        'priority_id' => 1
                    ];
                }
            }

            if (!empty($transactions)) {
                $generaliserModel->insererDonnees('transaction', $transactions);
            }
        }

        Flight::json(['success' => true, 'message' => 'Exercise updated successfully.']);
    }

    public function deleteExercise()
    {
        $id = Flight::request()->data->id;
        Flight::generaliserModel()->updateTableData('exercise', ['exercise_is_deleted' => 1], ['exercise_id' => $id]);
        Flight::json(['success' => true, 'message' => 'Exercise deleted successfully.']);
    }

    public function confirmTransaction()
    {
        $transactionId = Flight::request()->data->transaction_id;

        $generaliserModel = Flight::generaliserModel();
        $result = $generaliserModel->updateTableData('transaction', ['status' => 1], ['transaction_id' => $transactionId]);

        if ($result['status'] === 'success') {
            Flight::json(['success' => true, 'message' => 'Transaction confirmed successfully.']);
        } else {
            Flight::json(['success' => false, 'message' => 'Error confirming transaction.']);
        }
    }

    public function updateTransactions()
    {
        $generaliserModel = Flight::generaliserModel();
        $transactions = Flight::request()->data->transactions;

        foreach ($transactions as $budgetElementId => $periods) {
            foreach ($periods as $periodNum => $amounts) {
                foreach ($amounts as $nature => $amount) {
                    $transactionData = [
                        'amount' => $amount
                    ];

                    $generaliserModel->updateTableData('transaction', $transactionData, [
                        'budget_element_id' => $budgetElementId,
                        'exercise_id' => Flight::request()->data->exercise_id,
                        'period_num' => $periodNum,
                        'nature' => $nature
                    ]);
                }
            }
        }

        Flight::set('message', 'Transactions updated successfully.');
        Flight::redirect('admin');
    }

    public function importDepartmentsCsv()
    {
        $generaliserModel = Flight::generaliserModel();
        $csvFilePath = $_FILES['csv_file']['tmp_name'];

        $result = $generaliserModel->importCsv($csvFilePath);

        if ($result['status'] === 'success') {
            $data = $result['data'];
            $insertResult = $generaliserModel->insererDonnees('department', $data);
            Flight::set('message', 'Departments imported successfully.');
        } else {
            Flight::set('message', 'Error importing departments: ' . $result['message']);
        }

        Flight::redirect('admin');
    }

    public function importTypesCsv()
    {
        $generaliserModel = Flight::generaliserModel();
        $csvFilePath = $_FILES['csv_file']['tmp_name'];

        $result = $generaliserModel->importCsv($csvFilePath);

        if ($result['status'] === 'success') {
            $data = $result['data'];
            $insertResult = $generaliserModel->insererDonnees('type', $data);
            if ($insertResult['success']) {
                Flight::set('message', 'Types imported successfully.');
            } else {
                Flight::set('message', 'Error inserting types: ' . $insertResult['message']);
            }
        } else {
            Flight::set('message', 'Error importing types: ' . $result['message']);
        }

        Flight::redirect('admin');
    }

    public function importExercisesCsv()
    {
        $generaliserModel = Flight::generaliserModel();
        $csvFilePath = $_FILES['csv_file']['tmp_name'];

        $result = $generaliserModel->importCsv($csvFilePath);

        if ($result['status'] === 'success') {
            $data = $result['data'];
            foreach ($data as $exercise) {
                $result = $generaliserModel->insererDonnee('exercise', $exercise);
                $last_id = $generaliserModel->getLastInsertedId('exercise', 'exercise_id');
                if ($result['status'] == 'success') {
                    $exerciseId = $last_id['last_id'];
                    $budgetElements = $generaliserModel->getTableData('budget_element', ['budget_element_is_deleted' => 0]);
                    $transactions = [];
                    for ($i = 1; $i <= $exercise['nb_period']; $i++) {
                        foreach ($budgetElements as $element) {
                            $transactions[] = [
                                'budget_element_id' => $element['budget_element_id'],
                                'exercise_id' => $exerciseId,
                                'period_num' => $i,
                                'nature' => 1, // Prevision
                                'amount' => 0,
                                'status' => 1,
                                'priority_id' => 1 // Default priority
                            ];
                            $transactions[] = [
                                'budget_element_id' => $element['budget_element_id'],
                                'exercise_id' => $exerciseId,
                                'period_num' => $i,
                                'nature' => 2, // Realisation
                                'amount' => 0,
                                'status' => 1,
                                'priority_id' => 1 // Default priority
                            ];
                        }
                    }
                    if (!empty($transactions)) {
                        $generaliserModel->insererDonnees('transaction', $transactions);
                    }
                }
            }
            Flight::set('message', 'Exercises and transactions imported successfully.');
        } else {
            Flight::set('message', 'Error importing exercises: ' . $result['message']);
        }

        Flight::redirect('admin');
    }

    private function getBudgetData($exerciseId)
    {
        $generaliserModel = Flight::generaliserModel();
        $exercise = $generaliserModel->getTableData('exercise', ['exercise_id' => $exerciseId, 'exercise_is_deleted' => 0])[0];
        $nbPeriod = $exercise['nb_period'];
        $startBalance = $exercise['start_balance'];

        $budgetData = [
            'start_balance' => [],
            'nb_period' => $nbPeriod,
            'headers' => ['Category', 'Rubric'],
            'lines' => []
        ];
        $budgetData['start_balance'][] = $startBalance;

        for ($i = 1; $i <= $nbPeriod; $i++) {
            $budgetData['headers'][] = "Period $i (Prevision)";
            $budgetData['headers'][] = "Period $i (Realisation)";
            $budgetData['headers'][] = "Period $i (Ecart)";
        }

        $currentBalancePrevision = $startBalance;
        $currentBalanceRealisation = $startBalance;

        $categories = $generaliserModel->getTableData('category', []);
        foreach ($categories as $category) {
            $elements = $generaliserModel->getTableData('budget_element', ['category_id' => $category['category_id'], 'budget_element_is_deleted' => 0]);
            foreach ($elements as $element) {
                $department = $generaliserModel->getTableData('department', ['department_id' => $element['department_id']])[0]['name'] ?? 'Unknown';
                $type = $generaliserModel->getTableData('type', ['type_id' => $element['type_id']])[0]['name'] ?? 'Unknown';
                $line = [
                    'category' => $category['name'],
                    'description' => $element['description'],
                    'id' => $element['budget_element_id'],
                    'department' => $department,
                    'type' => $type
                ];

                for ($i = 1; $i <= $nbPeriod; $i++) {
                    $periodData = [
                        'prevision' => 0,
                        'realisation' => 0,
                        'ecart' => 0,
                        'statusR' => 0,
                        'statusP' => 0
                    ];

                    $transactions = $generaliserModel->getTableData('transaction', ['exercise_id' => $exerciseId, 'status' => 1, 'period_num' => $i, 'budget_element_id' => $element['budget_element_id']]);
                    foreach ($transactions as $transaction) {
                        if ($transaction['status'] == 1) {
                            if ($transaction['nature'] == 1) {
                                if ($category['category_id'] == 1) {
                                    $periodData['prevision'] += (float)$transaction['amount'];
                                    $periodData['statusP'] = $transaction['status'];
                                } else {
                                    $periodData['prevision'] -= (float)$transaction['amount'];
                                    $periodData['statusP'] = $transaction['status'];
                                }
                            } else {
                                if ($category['category_id'] == 1) {
                                    $periodData['realisation'] += (float)$transaction['amount'];
                                    $periodData['statusR'] = $transaction['status'];
                                } else {
                                    $periodData['realisation'] -= (float)$transaction['amount'];
                                    $periodData['statusR'] = $transaction['status'];
                                }
                            }
                        }
                    }

                    if ($category['category_id'] == 1) {
                        $periodData['ecart'] = $periodData['prevision'] - $periodData['realisation'];
                    } else {
                        $periodData['ecart'] = (-1 * $periodData['prevision']) - (-1 * $periodData['realisation']);
                    }
                    $line["period_{$i}_prevision"] = $periodData['prevision'];
                    $line["period_{$i}_realisation"] = $periodData['realisation'];
                    $line["period_{$i}_ecart"] = $periodData['ecart'];
                    $line["period_{$i}_statusP"] = $periodData['statusP'];
                    $line["period_{$i}_statusR"] = $periodData['statusR'];
                }

                $budgetData['lines'][] = $line;
            }
        }

        for ($i = 1; $i <= $nbPeriod; $i++) {
            $totalPrevision = 0;
            $totalRealisation = 0;

            foreach ($budgetData['lines'] as $index => $line) {
                if (isset($line["period_{$i}_prevision"])) {
                    $totalPrevision += (float)$line["period_{$i}_prevision"];
                    $totalRealisation += (float)$line["period_{$i}_realisation"];
                }
            }

            $endBalancePrevision = $currentBalancePrevision + $totalPrevision;
            $endBalanceRealisation = $currentBalanceRealisation + $totalRealisation;

            $budgetData['lines'][] = [
                'category' => 'Solde fin',
                'description' => '',
                'colspan' => 2,
                "period_{$i}_prevision" => $endBalancePrevision,
                "period_{$i}_realisation" => $endBalanceRealisation,
                "period_{$i}_ecart" => $endBalancePrevision - $endBalanceRealisation
            ];

            if ($i < $nbPeriod) {
                $budgetData['start_balance'][] = $endBalanceRealisation;
            }

            $currentBalancePrevision = $endBalanceRealisation;
            $currentBalanceRealisation = $endBalanceRealisation;
        }

        return $budgetData;
    }
    public function importTransactionsCsv()
    {
        if (isset($_FILES['transactions_csv']) && $_FILES['transactions_csv']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['transactions_csv']['tmp_name'];
            $generaliserModel = Flight::generaliserModel();
            $result = $generaliserModel->importCsv($fileTmpPath, ';');

            if ($result['status'] === 'success') {
                $data = $result['data'];
                foreach ($data as $row) {
                    $transactionData = [
                        'amount' => $row['amount'],
                        'status' => 0
                    ];
                    $conditions = [
                        'nature' => $row['nature'],
                        'exercise_id' => $row['exercise_id'],
                        'budget_element_id' => $row['budget_element_id'],
                        'period_num' => $row['period_num'],
                    ];
                    $generaliserModel->updateTableData('transaction', $transactionData, $conditions);
                }
                Flight::set('message', 'Transactions imported successfully.');
            } else {
                Flight::set('message', $result['message']);
            }
        } else {
            Flight::set('message', 'Error uploading file.');
        }
        Flight::redirect('admin');
    }

    public function exportBudgetDataCsv()
    {
        $exerciseId = Flight::request()->data->exercise;
        $budgetData = $this->getBudgetData($exerciseId);
        $csvData = [];
        $headers = ['Category', 'Rubric'];
        for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
            $headers[] = "Period $i (Prevision)";
            $headers[] = "Period $i (Realisation)";
            $headers[] = "Period $i (Ecart)";
        }
        $csvData[] = $headers;

        $startBalanceRow = ['Solde depart', ''];
        for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
            $startBalanceRow[] = $budgetData['start_balance'][$i - 1];
            $startBalanceRow[] = '';
            $startBalanceRow[] = '';
        }
        $csvData[] = $startBalanceRow;

        foreach ($budgetData['lines'] as $line) {
            if ($line['category'] !== 'Solde depart' && $line['category'] !== 'Solde fin') {
                $row = [];
                $row[] = $line['category'];
                $row[] = $line['description'];
                for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
                    if ((isset($line["period_{$i}_statusP"]) && $line["period_{$i}_statusP"] == 0) || (isset($line["period_{$i}_prevision"]) && $line["period_{$i}_prevision"] == 0)) {
                        $row[] = '';
                    } else {
                        $row[] = isset($line["period_{$i}_prevision"]) ? $line["period_{$i}_prevision"] : '';
                    }
                    if ((isset($line["period_{$i}_statusR"]) && $line["period_{$i}_statusR"] == 0) || (isset($line["period_{$i}_realisation"]) && $line["period_{$i}_realisation"] == 0)) {
                        $row[] = '';
                    } else {
                        $row[] = isset($line["period_{$i}_realisation"]) ? $line["period_{$i}_realisation"] : '';
                    }
                    $row[] = isset($line["period_{$i}_ecart"]) ? $line["period_{$i}_ecart"] : '';
                }
                $csvData[] = $row;
            }
        }

        $endBalanceRow = ['Solde fin', ''];
        for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
            $endBalanceRow[] = $budgetData['lines'][count($budgetData['lines']) - $budgetData['nb_period'] + $i - 1]["period_{$i}_prevision"];
            $endBalanceRow[] = $budgetData['lines'][count($budgetData['lines']) - $budgetData['nb_period'] + $i - 1]["period_{$i}_realisation"];
            $endBalanceRow[] = $budgetData['lines'][count($budgetData['lines']) - $budgetData['nb_period'] + $i - 1]["period_{$i}_ecart"];
        }
        $csvData[] = $endBalanceRow;

        $output = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="budget_data.csv"');
        foreach ($csvData as $row) {
            fputcsv($output, $row, ';');
        }
        fclose($output);
        exit;
    }

    public function exportBudgetDataPdf()
    {
        $exerciseId = Flight::request()->query->exercise;
        $budgetData = $this->getBudgetData($exerciseId);
        $exercise = Flight::generaliserModel()->getTableData('exercise', ['exercise_id' => $exerciseId, 'exercise_is_deleted' => 0])[0];
        $exerciseYear = date('Y', strtotime($exercise['start_date']));
        require('assets/fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, 'Budget Monitoring - ' . $exerciseYear, 0, 1, 'C');
        $pdf->Ln(10);
        $numColumns = 2 + 3 * $budgetData['nb_period'];
        $maxWidth = 190;
        $cellWidth = $maxWidth / $numColumns;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($cellWidth, 10, 'Category', 1, 0, 'C');
        $pdf->Cell($cellWidth, 10, 'Rubric', 1, 0, 'C');
        for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
            $pdf->Cell($cellWidth, 10, "P$i (P)", 1, 0, 'C');
            $pdf->Cell($cellWidth, 10, "P$i (R)", 1, 0, 'C');
            $pdf->Cell($cellWidth, 10, "P$i (E)", 1, 0, 'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($cellWidth, 10, 'Solde D', 1);
        $pdf->Cell($cellWidth, 10, '', 1);
        for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
            $pdf->Cell($cellWidth, 10, $budgetData['start_balance'][$i - 1], 1, 0, 'R');
            $pdf->Cell($cellWidth, 10, '', 1);
            $pdf->Cell($cellWidth, 10, '', 1);
        }
        $pdf->Ln();
        foreach ($budgetData['lines'] as $line) {
            if ($line['category'] !== 'Solde depart' && $line['category'] !== 'Solde fin') {
                $pdf->Cell($cellWidth, 10, $line['category'], 1);
                $pdf->Cell($cellWidth, 10, $line['description'], 1);
                for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
                    if ((isset($line["period_{$i}_statusP"]) && $line["period_{$i}_statusP"] == 0) || (isset($line["period_{$i}_prevision"]) && $line["period_{$i}_prevision"] == 0)) {
                        $pdf->Cell($cellWidth, 10, '', 1, 0, 'C');
                    } else {
                        $pdf->Cell($cellWidth, 10, isset($line["period_{$i}_prevision"]) ? $line["period_{$i}_prevision"] : '', 1, 0, 'R');
                    }
                    if (isset($line["period_{$i}_statusR"]) && $line["period_{$i}_statusR"] == 0 || (isset($line["period_{$i}_realisation"]) && $line["period_{$i}_realisation"] == 0)) {
                        $pdf->Cell($cellWidth, 10, '', 1, 0, 'C');
                    } else {
                        $pdf->Cell($cellWidth, 10, isset($line["period_{$i}_realisation"]) ? $line["period_{$i}_realisation"] : '', 1, 0, 'R');
                    }
                    $pdf->Cell($cellWidth, 10, isset($line["period_{$i}_ecart"]) ? $line["period_{$i}_ecart"] : '', 1, 0, 'R');
                }
                $pdf->Ln();
            }
        }
        $pdf->Cell($cellWidth, 10, 'Solde F', 1);
        $pdf->Cell($cellWidth, 10, '', 1);
        for ($i = 1; $i <= $budgetData['nb_period']; $i++) {
            $pdf->Cell($cellWidth, 10, $budgetData['lines'][count($budgetData['lines']) - $budgetData['nb_period'] + $i - 1]["period_{$i}_prevision"], 1, 0, 'R');
            $pdf->Cell($cellWidth, 10, $budgetData['lines'][count($budgetData['lines']) - $budgetData['nb_period'] + $i - 1]["period_{$i}_realisation"], 1, 0, 'R');
            $pdf->Cell($cellWidth, 10, $budgetData['lines'][count($budgetData['lines']) - $budgetData['nb_period'] + $i - 1]["period_{$i}_ecart"], 1, 0, 'R');
        }
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 10, '(Solde D) Solde depart    (Solde F) Solde fin     (P) Prevision    (R) Realisation    (E) Ecart', 0, 1, 'C');
        // $pdf->Output('D', "budget_data_{$exerciseYear}.pdf");
        $pdf->Output();
        exit;
    }

    public function exportListsPdf()
    {
        $generaliserModel = Flight::generaliserModel();
        $departments = $generaliserModel->getTableData('department', ['department_is_deleted' => 0]);
        $categories = $generaliserModel->getTableData('category', []);
        $types = $generaliserModel->getTableData('type', []);
        $budgetElements = $generaliserModel->getTableData('budget_element', ['budget_element_is_deleted' => 0]);
        require('assets/fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'All Lists', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Departments', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        foreach ($departments as $department) {
            $pdf->Cell(0, 10, '- ' . $department['name'], 0, 1);
        }
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Categories', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        foreach ($categories as $category) {
            $pdf->Cell(0, 10, '- ' . $category['name'], 0, 1);
        }
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Types', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        foreach ($types as $type) {
            $pdf->Cell(0, 10, '- ' . $type['name'], 0, 1);
        }
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Budget Elements', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        foreach ($budgetElements as $element) {
            $pdf->Cell(0, 10, '- ' . $element['description'], 0, 1);
        }
        // $pdf->Output('D', 'lists_export.pdf');
        $pdf->Output();
        exit;
    }
}
