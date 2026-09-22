---
paths:
  - '{app,domain,infra}/**/*.php'
---

# Appdomaininfra

## SOLID原則と責務のレビュー
クラスごとの変更理由を一つに保ち、Presentationは入力・HTTP状態、Serviceはユースケース、Domainは業務ルール、Infraは技術実装へ分離する。上位層は境界の抽象へ依存させるが、適用先のないSOLID原則のためにInterfaceや基底クラスを先回りして追加しない。PR本文には、実際に適用したSOLID原則、対象クラス、レビューで確認する責務境界を具体的に記載する。
