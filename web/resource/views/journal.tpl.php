<?php $this->extend('layout'); ?>
<?php $this->section('content'); ?>
<div class="card card-block radius-0 p-0">
    <div class="table-responsive">
        <table id="lyric-items" class="table table-hover mb-0">
            <thead class="thead-dark">
            <tr>
                <th width="80%" class="text-left">Message</th>
                <th class="text-left">Date</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($this->journal->valid()) { ?>
                <?php $this->total = 0; ?>
                <?php foreach ($this->journal as $item) { ?>
                    <tr>
                        <td class="text-left"><?php echo htmlspecialchars($item['message']); ?></td>
                        <td class="text-left"><?php echo $item['date']; ?></td>
                    </tr>
                    <?php $this->total++; ?>
                <?php } ?>
            <?php } ?>
            </tbody>
            <tfoot>
            <tr>
                <td>
                    <?php if ($this->get('total', 0) < 1) { ?>
                        <div>The journal is empty!</div>
                    <?php } else { ?>
                        <form method="POST" action="/journal/flush" role="form">
                            <button type="submit" name="flush" value="1" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i> Flush
                            </button>
                        </form>
                    <?php } ?>
                </td>
                <th class="text-right">
                    Total: <span data-element="total"><?php echo $this->get('total', 0); ?></span>
                </th>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php $this->endSection('content'); ?>
