<div class="page-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="color: var(--c-forest-dark); margin: 0 0 6px;">Internship Resources Administration</h1>
        <p class="text-muted" style="margin: 0;">
            Manage Internship Kit documents, Journey stages, Guidelines, and Categories (FR-IRM-05, FR-IRM-07).
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?= url('resource-admin/announcements') ?>" class="btn btn-secondary">
            📢 Announcements Admin
        </a>
        <a href="<?= url('resources') ?>" target="_blank" class="btn btn-secondary">
            👁️ Preview Student Portal
        </a>
    </div>
</div>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 30px;">
    <!-- Main Column: Documents CRUD (FR-IRM-05, FR-IRM-07) -->
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="color: var(--c-forest-dark); margin: 0;">Internship Kit Documents</h3>
                <button type="button" class="btn btn-primary btn-sm" onclick="openUploadModal()">
                    ＋ Upload New Document
                </button>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Document Title</th>
                            <th>Category</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resources)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-6 text-muted">No documents uploaded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resources as $res): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($res['title']) ?></strong><br>
                                        <small class="text-muted"><?= e($res['original_name'] ?: 'document') ?> (<?= !empty($res['size']) ? round((int)$res['size']/1024, 1) . ' KB' : 'PDF' ?>)</small>
                                    </td>
                                    <td><span class="badge badge-neutral"><?= e($res['category_name']) ?></span></td>
                                    <td>v<?= (int)$res['version'] ?></td>
                                    <td>
                                        <?php if ($res['status'] === 'published'): ?>
                                            <span class="badge badge-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge badge-neutral">Unpublished</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <!-- Toggle Publish Status -->
                                        <form action="<?= url('resource-admin/resources/toggle') ?>" method="POST" style="display: inline;">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$res['id'] ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm" style="margin-right: 4px;">
                                                <?= $res['status'] === 'published' ? 'Unpublish' : 'Publish' ?>
                                            </button>
                                        </form>

                                        <!-- Edit / Replace Modal Button -->
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='openEditModal(<?= json_encode($res) ?>)' style="margin-right: 4px;">
                                            Edit/Replace
                                        </button>

                                        <!-- Delete -->
                                        <form action="<?= url('resource-admin/resources/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Permanently delete this document?');">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$res['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Journey Stages Admin (FR-IRM-05) -->
        <div class="card" style="margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="color: var(--c-forest-dark); margin: 0;">Internship Journey Stages (FR-IRM-05)</h3>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('newJourneyForm').style.display='block'">
                    ＋ Add Journey Stage
                </button>
            </div>

            <div id="newJourneyForm" style="display: none; background: var(--c-bluegray); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                <form action="<?= url('resource-admin/journey/store') ?>" method="POST">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="grid" style="grid-template-columns: 100px 1fr 140px; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Stage #</label>
                            <input type="number" name="stage_no" class="form-control" value="<?= count($journeyStages) + 1 ?>" required>
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Stage Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Semester 1 – First Briefing">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Status</label>
                            <select name="status" class="form-control">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Description</label>
                        <textarea name="description" rows="2" class="form-control" required placeholder="Key milestones and actions required..."></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('newJourneyForm').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save Stage</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Stage</th>
                            <th>Title &amp; Milestone Details</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($journeyStages as $st): ?>
                            <tr>
                                <td><strong>Stage <?= (int)$st['stage_no'] ?></strong></td>
                                <td>
                                    <strong><?= e($st['title']) ?></strong>
                                    <div style="font-size: 0.85rem; color: #555;"><?= e($st['description']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $st['status'] === 'published' ? 'badge-success' : 'badge-neutral' ?>">
                                        <?= e(ucfirst($st['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <form action="<?= url('resource-admin/journey/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Delete this stage?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$st['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Guidelines Admin (FR-IRM-05) -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="color: var(--c-forest-dark); margin: 0;">Guidelines &amp; Policies (FR-IRM-05)</h3>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('newGuidelineForm').style.display='block'">
                    ＋ Add Guideline
                </button>
            </div>

            <div id="newGuidelineForm" style="display: none; background: var(--c-bluegray); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                <form action="<?= url('resource-admin/guidelines/store') ?>" method="POST">
                    <?= \App\Core\Csrf::field() ?>
                    <div class="grid" style="grid-template-columns: 160px 1fr 120px; gap: 12px; margin-bottom: 10px;">
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Kind</label>
                            <select name="kind" class="form-control" required>
                                <option value="eligibility">Eligibility</option>
                                <option value="procedure">Procedure</option>
                                <option value="responsibility">Responsibility</option>
                                <option value="instruction">Instruction</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Title</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Degree vs Diploma Credit Requirements">
                        </div>
                        <div>
                            <label class="form-label" style="font-size: 0.85rem;">Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <label class="form-label" style="font-size: 0.85rem;">Guideline Content Body</label>
                        <textarea name="body" rows="3" class="form-control" required placeholder="Policy text and detailed criteria..."></textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('newGuidelineForm').style.display='none'">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save Guideline</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kind</th>
                            <th>Guideline Title</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guidelines as $g): ?>
                            <tr>
                                <td><span class="badge badge-neutral"><?= e(ucfirst($g['kind'])) ?></span></td>
                                <td>
                                    <strong><?= e($g['title']) ?></strong>
                                    <div style="font-size: 0.85rem; color: #555;"><?= substr(e($g['body']), 0, 100) ?>...</div>
                                </td>
                                <td><span class="badge badge-success"><?= e(ucfirst($g['status'])) ?></span></td>
                                <td style="text-align: right;">
                                    <form action="<?= url('resource-admin/guidelines/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Delete this guideline?');">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Categories Admin (FR-IRM-07) -->
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <h3 style="color: var(--c-forest-dark); margin: 0 0 16px;">Resource Categories (FR-IRM-07)</h3>

            <form action="<?= url('resource-admin/categories/store') ?>" method="POST" style="margin-bottom: 16px;">
                <?= \App\Core\Csrf::field() ?>
                <div class="form-group" style="margin-bottom: 8px;">
                    <label class="form-label" style="font-size: 0.85rem;">New Category Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Briefing Slides">
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 0.85rem;">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                    ＋ Create Category
                </button>
            </form>

            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($categories as $cat): ?>
                    <li style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,0.06);">
                        <span><?= e($cat['name']) ?></span>
                        <form action="<?= url('resource-admin/categories/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Delete category?');">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 2px 8px; font-size: 0.75rem;">&times;</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Upload / Edit Resource Modal -->
<div id="resourceModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="color: var(--c-forest-dark); margin: 0;" id="modalResTitle">Upload Resource Document</h3>
            <button type="button" onclick="closeResModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #888;">&times;</button>
        </div>

        <form action="<?= url('resource-admin/resources/store') ?>" method="POST" enctype="multipart/form-data">
            <?= \App\Core\Csrf::field() ?>
            <input type="hidden" name="id" id="modalResId" value="0">

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="category_id" id="modalResCat" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Document Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="modalResTitleInput" class="form-control" required placeholder="e.g. Form 1 (Offer Letter Form)">
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Description / Instructions</label>
                <textarea name="description" id="modalResDesc" rows="2" class="form-control" placeholder="Brief explanation of document usage..."></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Document File (PDF, DOCX, PPTX) <span id="fileRequiredStar" class="text-danger">*</span></label>
                <input type="file" name="resource_file" class="form-control" accept=".pdf,.docx,.pptx">
                <small class="text-muted" id="fileHelpText">Uploading a replacement file automatically increments the document version (FR-IRM-07).</small>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Status</label>
                <select name="status" id="modalResStatus" class="form-control">
                    <option value="published">Published</option>
                    <option value="unpublished">Unpublished</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeResModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Document</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() {
    document.getElementById('modalResId').value = '0';
    document.getElementById('modalResTitle').innerText = 'Upload Resource Document';
    document.getElementById('modalResTitleInput').value = '';
    document.getElementById('modalResDesc').value = '';
    document.getElementById('fileRequiredStar').style.display = 'inline';
    document.getElementById('resourceModal').style.display = 'flex';
}
function openEditModal(res) {
    document.getElementById('modalResId').value = res.id;
    document.getElementById('modalResTitle').innerText = 'Edit / Replace: ' + res.title;
    document.getElementById('modalResTitleInput').value = res.title;
    document.getElementById('modalResDesc').value = res.description || '';
    document.getElementById('modalResCat').value = res.category_id;
    document.getElementById('modalResStatus').value = res.status;
    document.getElementById('fileRequiredStar').style.display = 'none';
    document.getElementById('resourceModal').style.display = 'flex';
}
function closeResModal() {
    document.getElementById('resourceModal').style.display = 'none';
}
</script>
