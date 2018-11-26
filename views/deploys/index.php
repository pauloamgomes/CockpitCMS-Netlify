<style>
.uk-modal-details .uk-modal-dialog {
  height: 85%;
}
</style>

<div>
  <ul class="uk-breadcrumb">
    <li class="uk-active"><span>@lang('Netlify Deploys')</span></li>
  </ul>
</div>

<div class="uk-margin-top" riot-view>

  @if($app->module('cockpit')->hasaccess('netlify', 'manage.view'))
  <div class="uk-form uk-clearfix" show="{!loading}">
    @if($app->module('cockpit')->hasaccess('netlify', 'manage.deploy'))
    <div class="uk-float-right">
      <a class="uk-button uk-button-primary uk-button-large" onclick="{createDeploy}">
        <i class="uk-icon-plus uk-icon-justify"></i> @lang('Deploy')
      </a>
    </div>
    @endif
  </div>

  <div class="uk-text-xlarge uk-text-center uk-text-primary uk-margin-large-top" show="{ loading }">
    <i class="uk-icon-spinner uk-icon-spin"></i>
  </div>

  <div class="uk-text-large uk-text-center uk-margin-large-top uk-text-muted" show="{ !loading && !deploys.length }">
    <img class="uk-svg-adjust" src="@url('netlify:icon.svg')" width="100" height="100" alt="@lang('Netlify Deploys')" data-uk-svg />
    <p>@lang('No deploys found')</p>
  </div>

  <div class="uk-modal uk-modal-details uk-height-viewport">
    <div class="uk-modal-dialog uk-modal-dialog-large">
      <a href="" class="uk-modal-close uk-close"></a>
      <h3>{ deploy && deploy.title }</h3>
      <div class="uk-margin uk-flex uk-flex-middle" if="{deploy}">
        <codemirror ref="codemirror" syntax="json"></codemirror>
      </div>
    </div>
  </div>

  <div class="uk-form uk-clearfix" show="{!loading}">
    <table class="uk-table uk-table-tabbed uk-table-striped uk-margin-top" if="{ !loading && deploys.length }">
      <thead>
        <tr>
          <th class="uk-text-small uk-link-muted uk-noselect" width="70">
            @lang('State')
          </th>
          <th class="uk-text-small uk-link-muted uk-noselect" width="450">
            @lang('Title')
          </th>
          <th class="uk-text-small uk-link-muted uk-noselect" width="120">
            @lang('Created')
          </th>
          <th class="uk-text-small uk-link-muted uk-noselect" width="120">
            @lang('Updated')
          </th>
          <th class="uk-text-small uk-link-muted uk-noselect" width="90">
            @lang('Deploy time')
          </th>
          <th class="uk-text-small uk-link-muted uk-noselect" width="60">
            @lang('Screenshot')
          </th>
        </tr>
      </thead>
      <tbody>
        <tr each="{deploy, $index in deploys}" class="{ deploy.state == 'error' ? 'uk-text-danger' : ''}">
          <td>
            <a onclick="{ showdeployDetails }" class="extrafields-indicator uk-text-nowrap">
              <span class="uk-badge uk-text-small" if="{!deploy.building && deploy.state !== 'error' && deploy.state !== 'ready' }"><i class="uk-icon-eye uk-icon-justify"></i>{ deploy.state }</span>
              <span class="uk-badge uk-text-small uk-badge-success" if="{deploy.state === 'ready'}"><i class="uk-icon-eye uk-icon-justify"></i>{ deploy.state }</span>
              <span class="uk-badge uk-text-small uk-badge-danger" if="{deploy.state === 'error'}"><i class="uk-icon-eye uk-icon-justify"></i>{ deploy.state }</span>
              <span class="uk-badge uk-text-small uk-badge-warning" if="{deploy.building}"><i class="uk-icon-spinner uk-icon-spin"></i>{ deploy.state }</span>
            </a>
          </td>
          <td>{ deploy.title }</td>
          <td><span class="uk-badge uk-badge-outline uk-text-muted">{ deploy.created_at }</span></td>
          <td><span class="uk-badge uk-badge-outline uk-text-muted">{ deploy.updated_at }</span></td>
          <td><span if="{deploy.deploy_time}">{ deploy.deploy_time }s</span></td>
          <td class="deploy-thumb">
            <a data-uk-lightbox="type:'image'" href="{ deploy.screenshot_url }" title="deploy screenshot: { deploy.title }" if="{deploy.screenshot_url}">
              <cp-thumbnail src="{ deploy.screenshot_url }" width="72" height="48"></cp-thumbnail>
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  @endif

  <script type="view/script">

    var $this = this;
    $this.deploy = {};
    $this.loading = true;
    $this.deploys = {{ json_encode($deploys) }};
    $this.building = {{ json_encode($building) }};

    this.on('mount', function() {
      $this.loading = false;
      $this.modal = UIkit.modal(App.$('.uk-modal-details', this.root), {modal:true});
      if ($this.building) {
        setTimeout(function() {
          $this.fetchData();
        }, 5000);
      }
      $this.update();
    });

    showdeployDetails(e) {
      $this.deploy = e.item.deploy;
      $this.modal.show();
      editor = $this.refs.codemirror.editor;
      editor.setValue(JSON.stringify($this.deploy, null, 2), true);
      editor.setOption("readOnly", true);
      editor.setSize($this.modal.dialog[0].clientWidth - 50, $this.modal.dialog[0].clientHeight - 70);
      editor.refresh();
      $this.trigger('ready');
    }

    createDeploy() {
      if ($this.building) {
        App.ui.notify(App.i18n.get("A deploy is already in progress, please wait until finishes."), "warning");
      } else {
        UIkit.modal.confirm("Triggering a new deploy on Netlify. Are you sure?", function() {
          App.callmodule('netlify:createDeploy').then(function(data) {
            App.ui.notify(App.i18n.get("A new deploy was requested."), "success");
            setTimeout(function() {
              App.ui.notify(App.i18n.get("Fetching deploy status..."), "success");
              $this.building = true;
              $this.fetchData();
            }, 3000)
          });
        });
      }
    }

    fetchData() {
      if (!this.building) {
        return;
      }
      App.callmodule('netlify:fetchDeploys').then(function(data) {
        if (data && data.result && data.result.deploys) {
          $this.deploys = data.result.deploys;
          $this.building = data.result.building;
          setTimeout(function() {
            $this.fetchData();
          }, 7500);
        } else {
          App.ui.notify(App.i18n.get("Cannot fetch deploys from Netlify! Try again later."), "danger");
        }
        $this.update();
      });
    }

  </script>

</div>
