# アーキテクチャ方針

会話で合意した方針。Laravelの推奨機能を使いながら、業務ロジックと永続化を分離する。過剰な抽象化は避ける。

## 技術構成

- Laravel、Blade＋Livewire。Reactは採用しない。
- 軽いブラウザ内操作には必要に応じてAlpine.jsを使う。
- テストはPest。整形はLaravel Pint。
- Laravel Boostを開発依存として導入し、インストール済みバージョンに対応する公式ドキュメントとガイドラインを使う。
- デプロイ先はLaravel Cloud。具体的なDB・リソース選択は予算見積もり時に決める。
- 動作基準はAndroid ChromeとPC Chrome。オンライン前提。

## ディレクトリと依存方向

```text
app/                         App\
  Http/                      Controller、HTTP入力、middlewareなど
  Livewire/                  画面状態、入力チェック、Service呼び出し
  Providers/                 Laravelの登録・DIの結線
  Service/
    Command/                 更新ユースケース
    Query/                   参照ユースケース、参照用の契約・DTO
domain/                      Domain\
                             業務ルール、エンティティ、値オブジェクト、Repositoryの契約
infra/                       Infra\
  Persistence/
    Eloquent/Models/         Eloquentモデル
    Mappers/                 EloquentモデルとDomainエンティティの相互変換
    Repositories/            Repository契約の実装（使用技術によらず配置）
database/                    migrations、factories、seeders
resources/views/             Bladeテンプレート
tests/                       Pestテスト
```

`app/App`、別のApplicationディレクトリ、Presentationディレクトリは作らない。Laravel標準の`database`は単数形。上記は配置先の規約であり、空の抽象クラスを先回りして作る理由にはしない。

- DomainはPHPの業務コードとし、Laravel・Livewire・Eloquent・App・Infraに依存させない。
- AppのユースケースはDomainや参照用インターフェースに依存する。認証済み利用者のIDを受け取り、処理と所有者確認を組み立てる。
- InfraはDomainのRepository契約やAppのQuery契約を実装する。Eloquent・SQL・外部サービスへのアクセスはここに置く。
- `app/Providers`、Laravelの設定、factory・seederは結線箇所としてInfraを参照できる。標準認証のEloquent UserもInfraに配置する。
- DomainのエンティティとEloquentモデルを同一にしない。画面の入力値やEloquent BuilderをDomainへ渡さない。
- 単なる一覧取得のためにDomainエンティティを組み立てない。QueryのDTOなど、必要な表示データを返す。

## Repositoryの配置と命名

Repository実装は`infra/Persistence/Repositories`へ配置する。Eloquentモデルは`infra/Persistence/Eloquent/Models`へ配置し、役割で分ける。これはLaravel標準の必須構成ではなく、Tsumuのプロジェクト規約である。

- 契約：`Domain\User\UserRepository`（`domain/User/UserRepository.php`）。
- 実装：`Infra\Persistence\Repositories\UserRepository`（`infra/Persistence/Repositories/UserRepository.php`）。
- 実装名に`Eloquent`などの技術名は原則付けない。契約と実装はnamespaceで区別し、同じファイルで参照するときは契約を`UserRepositoryContract`などのimport別名で区別する。
- Eloquent、Query Builder、生SQLのどれを使ってもRepositoryの配置は共通とする。内部の使用技術を変えても、呼び出し側の契約や実装クラス名を変えずに済むようにする。
- 異なる実装を同時に持つ必要が生じた段階で、区別する名前を検討する。将来の差し替えだけを理由に実装・ディレクトリ・汎用基底クラスを先回りして増やさない。
- Serviceは契約へ依存し、Providerで実装を結び付ける。Eloquent ModelやBuilderをRepositoryの契約から上位層へ漏らさない。単なるDB参照をすべてRepositoryにせず、一覧・集計は既定のQuery契約とDTOを使う。

## 永続化モデルとDomainの変換

EloquentモデルとDomainエンティティを相互変換するMapperは`infra/Persistence/Mappers`へ配置する。Repositoryは取得・保存の調整に集中し、具体的な属性変換をMapperへ委譲する。

- Mapperは対応する集約ごとに`EquipmentMapper`のように命名し、EloquentモデルからDomainへの復元と、Domainから永続化用属性への変換を担当する。
- DomainエンティティはDBカラムやEloquentモデルを知らない。`fromDatabaseRow`のような永続化固有の生成処理をDomainへ置かない。
- EloquentモデルはcastやリレーションなどEloquent固有の定義に集中し、`toDomain`を持たせない。Domainの構造変更をEloquentモデルの責務にしないためである。
- 変換が短く利用箇所が一つでも、Repositoryへ直接埋め込まずMapperを用意する。新しいRepositoryを実装するときに変換責務を見落とさず、配置を統一することを優先する。
- 一覧・集計などDomainエンティティを必要としない参照はMapperを経由せず、QueryのDTOへ必要な値を直接投影してよい。

この配置は、保存処理を技術によらず同じ場所から探せることを優先する。技術別に全実装をまとめて探す利便性より、役割の分かりやすさを選ぶ。汎用性を支えるのはフォルダ名ではなく、契約が永続化技術へ依存しないことである。

命名・責務・依存方向に複数の有力な選択肢がある場合、エージェントは利点と欠点を示して相談する。提案を合意済みの規約として記録しない。CLIの入力検証の配置は別途すり合わせ中である。

## 小さなCommand／Query分離

Commandは「開始する」「セットを記録する」「終了する」などの更新処理、Queryは「前回実績を取得する」「履歴を取得する」などの参照処理。

DBは共通とする。専用Command Bus、イベントソーシング、読み書き別DB、汎用Repository基底クラスは初期導入しない。必要なユースケースからクラスを追加する。

Queryが複数のDTOを返す場合は、DTOであることが名前と配置から分かるよう`Dto`ディレクトリへ置き、`EquipmentListItemDto`のように命名する。戻り値は生の配列ではなく、対応する`EquipmentListItemCollection`のような型付きCollectionとする。

Query DTOのCollectionは`App\Service\Query\Dto\TypedList`を共通基底とし、要素型の検証、反復、件数、空判定、条件検索を集約する。現時点では共通化の利用先が一つのためYAGNIには反するが、今後のQuery実装で一覧型と操作を統一し、設計を早期に確認するという明示的な判断で導入する。Domain CollectionやEloquent Collectionへは適用せず、Query DTOの戻り値だけを対象とする。

## Livewireの責務

Livewireは主にサーバー側PHPで画面状態や操作を扱い、ブラウザとの通信・差分更新を仲介する。PHPクラスは`app/Livewire`、Bladeは`resources/views`へ分離する方針。

LivewireとControllerは入力チェック、認証・認可の入口、Service呼び出し、結果の表示を担当する。業務ルール、直接のDB読み書き、目標計算を入れない。Bladeでクエリを実行しない。

ログインでは、入力値・正規化・バリデーションルールを`app/Livewire/Forms/LoginForm`へ分離し、Livewireコンポーネントは認証フローの調整に集中する。Form ObjectはPresentationの入力境界であり、認証処理や業務ルール、DBアクセスは持たせない。認証情報の照合は`app/Service/Command`へ委譲し、IPを含む試行制限、認証成功後のセッション再生成、エラー表示はリクエスト境界で扱う。

業務データを作成・更新するForm Objectから検証済み入力を別クラスへ渡す場合は、生の連想配列や検証前にも呼べる個別getterではなく、入力ごとの不変DTOを返す。DTOは`app/Livewire/Forms/Dto`へ配置し、`ValidatedEquipmentInputDto`のように検証済み入力であることを命名で示す。Form Objectが正規化・検証と入力用enumへの変換までを担当し、DTOには認証済みUser IDなどクライアント入力ではない値を含めない。これにより、Presentation境界からServiceへ渡す値の種類を実行時の型でも明示する。

ログイン済みであることの確認は、保護対象のルートへLaravel標準の`auth`ミドルウェアを適用して共通化する。ログイン画面には`guest`ミドルウェアを適用し、各LivewireコンポーネントやControllerで同じ認証チェックを繰り返さない。特定データを操作できるかという所有者確認はログイン状態とは別の認可として、ServiceやPolicyで扱う。

ログアウトは専用のinvokable ControllerでGuard、セッション破棄、CSRFトークン再生成を扱う。TodayのLivewireコンポーネントへセッション終了処理を混在させない。

重量の増減、開閉などは必要に応じてブラウザ側で即応させる。セット完了時に保存し、成功を確認してから保存済み表示にする。通信エラーで入力を失わず、保存の連打・再送による重複を防ぐ仕様を記録機能の実装時に用意する。常時ポーリングは基本構成に含めない。

## Laravelの標準機能との関係

認証、セッション、CSRF対策、入力検証、DI、ルーティング、migration、factoryなどはLaravelの標準機能を使う。独自認証や独自コンテナを作らない。

Laravelがモデルを`App\Models`に生成する場合も、このプロジェクトのモデル配置に合わせ、namespace、認証設定、factoryの対応を更新する。非標準namespaceのfactoryはモデルとの関連を明示する。

トランザクションやDB制約で保存の整合性を保つ。必要な境界は実際の更新処理を作る際に決める。

## User境界

個人利用でもユーザー紐づけを省略しない。リクエストから渡された`user_id`を所有者として信用しない。QueryとCommandの両方で認証済みユーザーの範囲に限定する。画面の表示制御だけで保護しない。別ユーザーのIDを指定する参照・更新のテストを用意する。

## 識別子

User、Equipment、Exercise、Workoutなど、TsumuのDomainデータの主キーにはUUIDv7を使い、外部キーもUUID型に揃える。Laravel 13のEloquentモデルでは`HasUuids`を使い、時系列に沿ったUUIDv7を生成する。内部連番IDと公開UUIDを二重には持たない。

`jobs`、`job_batches`、`cache`、migration管理などLaravel内部のデータ構造は対象外とし、フレームワーク標準の識別子を維持する。UUIDは認可の代替ではないため、推測しにくいIDであってもUser所有者境界を必ず確認する。

Domain境界を越えてIDを扱う場合は、プリミティブな文字列の取り違えを防ぐため、`UserId`や`EquipmentId`など用途別の値オブジェクトを使う。UUIDv7の形式検証・正規化・不正値の例外は共通基底クラス`Domain\Shared\UuidV7Id`へ集約する。用途別IDは型を区別するだけの小さなクラスとし、具体的な利用箇所ができたものから追加する。

## テスト

- DomainのルールはLaravelを起動しないPestのUnitテストで検証する。
- 保存、取得、認証、User境界、Livewireからの操作はFeatureテストで検証する。
- Repository、QueryのSQL、DB制約など永続化の契約はDbIntegrationテストで検証する。FeatureとDbIntegrationは検証する責務で分ける。FeatureでDBを使うことは禁止しない。
- factoryで各テストのデータを用意する。
- 未実装の仕様をテストで勝手に確定しない。
- 変更に必要な最小限の検証を実行する。DBが確定したら、DB固有の整合性・クエリは本番と同じ種類のDBでも検証する。

詳しい区分、ブラウザテストの選択肢、実行方法は [テスト方針](testing.md) を参照する。1 Stepの手書き差分は追加＋削除で原則400行以内。テスト差分は別枠として報告する。

## 費用とデプロイ

月額約US$5が目標。初回起動の待ち時間は許容する。開発はローカル、本番はまず1環境。アプリとDBの休止を検討し、常駐worker、追加キャッシュサーバー、有料UI部品は必要になるまで追加しない。

利用料はDB保存領域・稼働時間・転送量などにも依存するため、$5以内を保証しない。契約時の料金と実際の使用量で確認する。DB製品・リージョン・バックアップ・支出制限はデプロイ前に確定する。ローカルSQLiteは開発用の暫定設定であり、本番DBの決定ではない。

Boostは`require-dev`で管理する。本番は`composer install --no-dev`とし、Boost MCPや開発ツールを公開しない。Cloudの契約・リソース作成・デプロイはまだ行っていない。

## ローカルDocker環境

ローカルで同じ実行環境を再現するためLaravel Sailを使う。アプリコンテナはPHP 8.5とNode 24を使用し、DBは当面SQLiteを継続する。MySQL、Redis、常駐workerなど、現在の機能に不要なサービスは追加しない。Sailは開発依存とし、本番のLaravel Cloud構成には含めない。

## 公式情報

- [Laravel Boost](https://laravel.com/docs/13.x/boost)
- [Livewire components](https://livewire.laravel.com/docs/4.x/components)
- [Laravel Cloud料金](https://laravel.com/cloud/docs/pricing)

料金やAPIを実装時の記憶だけで決めず、インストール済みバージョンと公式情報で確認する。
