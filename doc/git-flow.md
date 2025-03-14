# Git-flow ワークフローの概要

Git-flow は、Git リポジトリの管理を体系的に行うためのブランチ戦略です。以下は基本的な流れとブランチの役割を説明します。

---

## メインブランチ

1. **`main`**
   - 本番環境のコードを保持する。
   - デプロイ可能な状態のみを含む。
2. **`develop`**
   - 次のリリースに向けた開発を進めるブランチ。
   - 新しい機能や修正はここに統合される。

---

## サポートブランチ

### 1. **Feature ブランチ**

- 新機能を開発するためのブランチ。
- `develop`ブランチから派生し、完了後に`develop`へマージ。

```bash
git checkout develop
git checkout -b feature/<feature-name>

git checkout develop
git merge feature/<feature-name>
git branch -d feature/<feature-name>

```

### 2. **Release ブランチ**

リリース準備用のブランチ（バグ修正、リリースに向けた調整）。
develop から派生し、main にマージ後にタグ付け。

```bash
git checkout develop
git checkout -b release/<version-number>

 必要な調整を行ったら：

git checkout main
git merge release/<version-number>
git tag <version-number>
git checkout develop
git merge release/<version-number>
git branch -d release/<version-number>
```

### 3. **Hotfix ブランチ**

- 本番環境の緊急修正用。
- main から派生し、修正後は main と develop の両方にマージ。

```bash
git checkout main
git checkout -b hotfix/<fix-name>

    修正が完了したら：

git checkout main
git merge hotfix/<fix-name>
git tag <version-number>
git checkout develop
git merge hotfix/<fix-name>
git branch -d hotfix/<fix-name>
```

### 基本的な流れ

**開発**

- 新しい機能は feature/ブランチで作業。
- 完了後に develop へマージ。
**リリース準備**
- リリース準備が整ったら、release/ブランチを作成。
- 必要な調整を行い main にマージ。
**本番リリース**
- main にマージ後、タグ付けしてリリース。
- 緊急修正
- 本番環境で問題が発生した場合、hotfix/ブランチで対応。
