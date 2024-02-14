<?php require_once __DIR__ . "/../header.php"; ?>

<div class="container">
    <div class="row">
        <div class="col-md-12 page-header d-flex justify-content-between align-items-center">
            <h2 class="page-title"><?= $device ?> Videos</h2>
            <button class="btn btn-dark" data-toggle="modal" data-target="#videosModal"><i class="fas fa-plus"></i></button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="content">
                    <div class="canvas-wrapper">
                        <table class="table table-striped">
                            <thead class="success">
                                <tr>
                                    <th></th>
                                    <th class="text-end">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody class="files-class">
                                <?php if (count($videos) > 0): ?>
                                    <?php foreach ($videos as $video): ?>
                                        <tr>
                                            <td><a href="/videos?client=<?= $video->getClientID(); ?>&id=<?= $video->getID(); ?>"><?= $video->getFilename(); ?></a></td>
                                            <td class="text-end"><?= $video->getTimestamp(); ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <tr>
                                        No videos
                                    </tr>
                                <?php endif ?>               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="videosModal" tabindex="-1" role="dialog" aria-labelledby="videosModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="videos-modal-title" id="videosModalLabel">Videos</h5>
          
        </div>
        <div class="modal-body">
          
        </div>
      </div>
    </div>
</div>

<?php require_once __DIR__ . "/../footer.php"; ?>