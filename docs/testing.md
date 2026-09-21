# テスト方針

## 合意事項と提案の区別

PestのUnit・Feature・DbIntegrationを用意する。ブラウザテストも導入する方針だが、以下のランナー比較は提案であり、まだブラウザ用パッケージは導入していない。

## テストの役割

| 種別 | 配置 | 検証するもの | 実行環境 |
| --- | --- | --- | --- |
| Unit | `tests/Unit` | 値の制約、状態遷移、目標計算などDomainのルール | PHPのみ、Laravel・DBを起動しない |
| Feature | `tests/Feature` | HTTP、認証・認可、入力検証、Livewire操作、ユースケースの連携 | Laravel。必要ならDBも使う |
| DbIntegration | `tests/DbIntegration` | Repository、Query、保存・再取得、所有者条件、ソート順、DB制約 | Laravel＋専用テストDB |
| Browser / E2E | ランナー決定時に配置を確定 | ブラウザで入力・クリック・通信・再描画・遷移が連動すること | 実ブラウザ＋テスト用アプリ・DB |

同じ境界値の全パターンを全層で重複させない。Unitで業務ルールの組み合わせ、DbIntegrationで永続化の契約、Featureで入口と所有者保護、Browserで主要な操作の連動を検証する。

たとえば「他ユーザーのセットを変更できない」はFeatureの重要なケース。「前回実績Queryが別ユーザーの記録を混ぜない」はDbIntegrationの別の契約。双方に価値がある。

## 現在のPest設定

`tests/Pest.php`でFeatureはLaravelのTestCase、DbIntegrationはLaravelのTestCase＋RefreshDatabaseを使用する。UnitはPHPUnitの基底TestCaseで実行する。Pestが内部でPHPUnitを使うことと、PHPUnit形式でテストを書くことは別であり、テスト記述はPestに統一する。

```sh
composer test
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --testsuite=DbIntegration
php artisan test tests/DbIntegration/Infra/UserProviderTest.php
```

今はインメモリSQLiteで基盤を確認している。これは本番DB固有の動作の保証ではない。Cloud用DBを選んだ段階でDbIntegrationを同じ種類・互換バージョンのDBで実行する構成を追加する。照合順序、decimal、日時、制約、ロック・競合などはそこで確認する。

テストは開発・本番データを利用しない。専用DBを用意し、破壊的なDBリセットの対象をテスト環境に限定する。RefreshDatabaseのトランザクションで見えなくなる競合は、必要になった時点で専用の検証を設計する。

## LivewireのFeatureテストで分かること

PestからLivewireのテストAPIを使い、入力値を設定し、アクションを呼び出し、検証エラー・表示内容・保存結果を確認できる。

ただしブラウザのJavaScriptを実際に動かすものではないため、Alpine.js、通信後のDOM更新、フォーカス、モバイル表示の確認にはBrowserテストが必要。

## ブラウザテストの選択肢

| 選択肢 | 記述言語 | 利点 | 検討点 |
| --- | --- | --- | --- |
| Playwright Test | TypeScriptまたはJavaScript | ブラウザ中心のテスト、trace・失敗時の画像、端末設定、通信制御を直接扱える | テストデータ準備、アプリ起動、DBの分離を別途組み立てる |
| Pest Browser＋Playwright | PHP | Pestの書き方とLaravelのfactoryを活用し、実ブラウザも検証できる | NodeとPlaywrightも必要。必要な通信制御などがプラグインで表現できるか確認する |

**Tsumuへの推奨案はPest Browser＋Playwright。** PHP中心の開発と少数の重要なE2Eに合う。PHPで書くことを優先せず、ブラウザの詳細制御やPlaywrightのツールを直接使いたい場合はPlaywright Testを選ぶ。両方は初期導入しない。

「Pestはバックエンド専用」とは限らない。Pest BrowserはPlaywrightを使う拡張で、実ブラウザを操作できる。既存Pestとの互換性を導入時に確認する。

## 最初に追加するブラウザテスト

1. ログインしてTodayへ移動できる。
2. レッグプレスを選び、重量・回数を入力して1セット保存できる。
3. ブラウザを再読み込みしても、保存したセットが残っている。
4. 前回実績を見ながら、次のトレーニングを記録できる。
5. 保存失敗時に未保存と分かり、入力が残る。再送しても二重に記録されない。

対応する機能ができたStepで、その機能のテストを追加する。一度に全画面のE2Eを作らない。

## Chromeとモバイルの確認

- 自動化はChromiumを基本とし、PCとAndroid相当の画面幅・タッチ設定で実行する。
- Chromiumの端末エミュレーションは実機Android Chromeそのものではない。リリース前には実際のAndroid Chromeでも入力・スクロール・ソフトキーボード表示を確認する。
- Google Chromeそのものを自動化対象にする必要があれば、対応するランナーのChromeチャンネル設定を導入する。
- 見た目が安定してから主要画面のスクリーンショット比較を検討する。初期段階で全画面の画像差分を必須にはしない。
- 独立した複雑なJavaScriptロジックが増えた場合にVitest等を検討する。現時点では追加しない。

## CIへの導入順

1. Unit・Feature・DbIntegrationとPint、フロントのビルド（Step 0-2）。
2. 本番DBと同じ種類のDbIntegration。
3. ブラウザのスモークテスト1本。
4. トレーニングの重要な操作と再開を検証する少数のE2E。

通常の画面表示・入力エラーはFeatureで素早く確認し、BrowserはJavaScriptを含めた連動に集中する。テスト差分は400行の枠から除外できるが、レビュー時には本体とテストそれぞれの行数を示す。

### Step 0-2の実行内容

[CIワークフロー](../.github/workflows/ci.yml)は、`main`向けPRの作成・更新と`main`へのPushで実行する。PRのChecksで以下を個別に確認できる。

| チェック | 実行内容 |
| --- | --- |
| Pest (Unit) | `vendor/bin/pest --ci --testsuite=Unit` |
| Pest (Feature) | `vendor/bin/pest --ci --testsuite=Feature` |
| Pest (DbIntegration) | `vendor/bin/pest --ci --testsuite=DbIntegration` |
| Pint | `vendor/bin/pint --test`（変更せず、不整形なら失敗） |
| Build | `npm ci` → `npm run build` |

Ubuntu 24.04・PHP 8.5・Node 24を使用。Composer/npmのlockファイルから依存を復元する。テスト用APP_KEYは実行ごとに生成し、DBは`phpunit.xml`のインメモリSQLiteを使う。本番DBやCloudの認証情報は不要。

チェックは独立して実行し、一つのsuiteが失敗しても残りの結果を確認できる。同一PRの古い実行は追加Pushでキャンセルし、各ジョブは10分でタイムアウトする。外部ActionsはコミットSHAで固定している。

失敗時はPRのChecksから該当ジョブのログを開き、上記と同じコマンドでローカル再現する。Pintの修正はローカルで`vendor/bin/pint`を実行し、差分を確認してPushする。CIは自動修正・コミット・デプロイを行わない。

このStepはチェックの実行・表示まで。マージをブロックするGitHubのブランチ保護設定、本番と同じDB、ブラウザテストは含めない。

## 公式資料

- [Livewire Testing](https://livewire.laravel.com/docs/4.x/testing)
- [Pest Browser Testing](https://pestphp.com/docs/browser-testing)
- [Playwright Emulation](https://playwright.dev/docs/emulation)
- [Playwright Browsers](https://playwright.dev/docs/browsers)
