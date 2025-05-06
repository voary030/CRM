<div class="container">
    <div class="page-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                    <div>
                        <h3 class="fw-bold mb-3">Statistiques</h3>
                        <h6 class="op-7 mb-2">Réactions Clients</h6>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <!-- Type Reactions CRUD -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-round">
                            <div class="card-header">
                                <div class="card-head-row">
                                    <div class="card-title">Gérer les Types de Réactions</div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Form for Creating/Updating Type Reactions -->
                                <form method="post" action="<?= htmlspecialchars(Flight::get('flight.base_url') . '/reaction-client/type-reaction/save') ?>" id="typeReactionForm">
                                    <input type="hidden" name="id" id="type_reaction_id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="description">Description</label>
                                                <input type="text" name="description" id="type_reaction_description" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="besoin_validation">Besoin Validation</label>
                                                <select name="besoin_validation" id="besoin_validation" class="form-control">
                                                    <option value="0">Non</option>
                                                    <option value="1">Oui</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary btn-sm mt-4">Enregistrer</button>
                                            <button type="button" class="btn btn-secondary btn-sm mt-4" onclick="resetTypeReactionForm()">Annuler</button>
                                        </div>
                                    </div>
                                </form>
                                <!-- Table for Type Reactions -->
                                <div class="table-responsive mt-4">
                                    <table id="typeReactionsTable" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th>Besoin Validation</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($typeReactions as $type): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($type['description']) ?></td>
                                                    <td><?= $type['besoin_validation'] ? 'Oui' : 'Non' ?></td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm" onclick="editTypeReaction(<?= $type['id'] ?>, '<?= htmlspecialchars($type['description']) ?>', <?= $type['besoin_validation'] ?>)">Modifier</button>
                                                        <form action="<?= htmlspecialchars(Flight::get('flight.base_url') . '/reaction-client/type-reaction/delete') ?>" method="post" style="display:inline;">
                                                            <input type="hidden" name="id" value="<?= $type['id'] ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reactions CRUD -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-round">
                            <div class="card-header">
                                <div class="card-head-row">
                                    <div class="card-title">Gérer les Réactions</div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Form for Creating/Updating Reactions -->
                                <form method="post" action="<?= htmlspecialchars(Flight::get('flight.base_url') . '/reaction-client/reaction/save') ?>" id="reactionForm">
                                    <input type="hidden" name="id" id="reaction_id">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="action_id">Action</label>
                                                <select name="action_id" id="action_id" class="form-control" required>
                                                    <option value="">Sélectionner une action</option>
                                                    <?php foreach ($actions as $action): ?>
                                                        <option value="<?= $action['id'] ?>"><?= htmlspecialchars($action['description']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="type_reaction_id">Type Réaction</label>
                                                <select name="type_reaction_id" id="reaction_type_id" class="form-control" required>
                                                    <option value="">Sélectionner un type</option>
                                                    <?php foreach ($typeReactions as $type): ?>
                                                        <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['description']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="montant">Montant (€)</label>
                                                <input type="number" step="0.01" name="montant" id="montant" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="statut">Statut</label>
                                                <select name="statut" id="statut" class="form-control" required>
                                                    <option value="en attente">En attente</option>
                                                    <option value="valide">Validé</option>
                                                    <option value="rejete">Rejeté</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary btn-sm mt-4">Enregistrer</button>
                                            <button type="button" class="btn btn-secondary btn-sm mt-4" onclick="resetReactionForm()">Annuler</button>
                                        </div>
                                    </div>
                                </form>
                                <!-- Table for Reactions -->
                                <div class="table-responsive mt-4">
                                    <table id="reactionsTable" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Action</th>
                                                <th>Type Réaction</th>
                                                <th>Montant (€)</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reactions as $reaction): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($reaction['action_description']) ?></td>
                                                    <td><?= htmlspecialchars($reaction['type_reaction_description']) ?></td>
                                                    <td><?= htmlspecialchars($reaction['montant']) ?></td>
                                                    <td><?= htmlspecialchars($reaction['statut']) ?></td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm" onclick="editReaction(<?= $reaction['id'] ?>, <?= $reaction['action_id'] ?>, <?= $reaction['type_reaction_id'] ?>, <?= $reaction['montant'] ?>, '<?= $reaction['statut'] ?>')">Modifier</button>
                                                        <form action="<?= htmlspecialchars(Flight::get('flight.base_url') . '/reaction-client/reaction/delete') ?>" method="post" style="display:inline;">
                                                            <input type="hidden" name="id" value="<?= $reaction['id'] ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmer la suppression ?')">Supprimer</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Sections (Filters, Frequencies, etc.) -->
                <!-- ... Keep your existing code for filters, frequencies, and age range sections ... -->
            </div>
        </div>
    </div>
</div>

<script>
function resetTypeReactionForm() {
    document.getElementById('typeReactionForm').reset();
    document.getElementById('type_reaction_id').value = '';
}

function editTypeReaction(id, description, besoin_validation) {
    document.getElementById('type_reaction_id').value = id;
    document.getElementById('type_reaction_description').value = description;
    document.getElementById('besoin_validation').value = besoin_validation ? '1' : '0';
}

function resetReactionForm() {
    document.getElementById('reactionForm').reset();
    document.getElementById('reaction_id').value = '';
}

function editReaction(id, action_id, type_reaction_id, montant, statut) {
    document.getElementById('reaction_id').value = id;
    document.getElementById('action_id').value = action_id;
    document.getElementById('reaction_type_id').value = type_reaction_id; // Updated ID
    document.getElementById('montant').value = montant;
    document.getElementById('statut').value = statut;
}
</script>