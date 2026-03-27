# Rapport sur les Problèmes de Migration

**Date du rapport:** 26 mars 2026  
**Environnement:** Symfony 7.3 avec Doctrine ORM  
**Base de données:** MySQL via MAMP

---

## 📊 Résumé Exécutif

- ✅ **Migrations appliquées:** 87
- ⚠️ **Migrations non disponibles (orphelines):** 27
- ❌ **Migrations non appliquées (en attente):** 2
- 🔴 **État du schéma:** **DÉSYNCHRONISÉ** 

**Verdict:** Le schéma de la base de données n'est **pas en sync** avec les entités Doctrine définies en code.

---

## 🔍 Détails des Problèmes

### Problème 1: Migrations Orphelines (27)

**Cause:** Des fichiers de migration ont été exécutés dans la base de données, mais les fichiers PHP correspondants **n'existent plus** dans le dossier `/migrations/`.

**Migrations affectées:**
- Version20251108144521 → Version20251108172401 (3 versions supprimées)
- Version20260130131549 → Version20260130132300 (3 versions supprimées)
- Version20260207131000 → Version20260207153813 (9 versions supprimées)
- Version20260217100000 → Version20260217101000 (2 versions supprimées)
- Version20260218100000 → Version20260218151225 (5 versions supprimées)
- Version20260324160000 (1 version supprimée)

**Impact:** 
- Doctrine ne peut pas les relire ou les annuler
- La table `doctrine_migration_versions` garde les traces
- Impossible de réinitialiser complètement la BD sans éditer directement la table

**Solutions disponibles:**
1. **Ignorer** (option recommandée si la BD fonctionne) — aucune action
2. **Nettoyer manuellement** — Supprimer les entrées de `doctrine_migration_versions` pour ces versions
3. **Recréer les fichiers PHP** — Reproduire les migrations en fichiers (complexe)

---

### Problème 2: Migrations Non Appliquées (2)

**Migrations en attente:**
1. `Version20260326145648` (non appliquée)
2. `Version20260326145649` (non appliquée)

**Contenu:** Ces deux migrations sont **identiques** et créent une table `Type_DEV` puis **suppriment massivement des tables:**
- `aeration`
- `Caisson_PVC`
- `conf_aeration`
- `conf_teinte_tablier`
- `Conf_volet_BLOC_N_R_iD4`
- `Lignes_de_commande_BLOC_N_R_iD4`
- `nuancier_standard`
- `Option Moteur-Filaire_Bubendorff`
- `Option_pack_SAV`
- `Options_Moteur_Radio_Bubendorff`
- `Specificites_caisson`
- `Tablier`
- `teinte_encadrement`
- `teinte_encadrement_elargi`
- `teinte_encadrement_specifique`

**Problèmes:**
1. **Duplication:** Les deux fichiers contiennent exactement le même code SQL
2. **Destruction destructrice:** Ces migrations suppriment une grande partie du schéma relatif aux volets (shutters)
3. **Impact majeur:** Les données configurées pour `conf_volet` seraient perte

**⚠️ ATTENTION:** Ces migrations ne doivent **PAS** être appliquées sans vérifier d'abord si elles sont intentionnelles.

---

### Problème 3: Désynchronisation du Schéma

**Diagnostic Doctrine:**
```
✅ Mapping: OK - Les entités PHP sont syntaxiquement correctes
❌ Base de données: ERREUR - Le schéma n'est pas en sync avec les entités
```

**Causes probables:**
1. Les migrations orphelines ont laissé des tables/colonnes résiduelles
2. Certaines modifications directes à la BD sans passer par les migrations
3. Conflits entre les entités Doctrine et le schéma réel

---

## 📋 Historique Récent

### Dernières migrations appliquées avec succès:
```
Version20260325144000 (25 mars 2026 14:45:30)
│ → Ajout des champs de configuration volet dans conf_volet
```

### Types de migrations exécutées:
- **Changements structure:** Ajout/suppression de colonnes, création de tables
- **Données:** Insertion de teintes, options, types de coulisse
- **Relationnels:** Modification des clés étrangères et cas en cascade

---

## 🛠️ Actions Recommandées

### Priorité 1: Vérifier l'intégrité
```bash
# Voir le liste détaillée des migrations
php bin/console doctrine:migrations:list

# Valider le mapping JPA/Entités
php bin/console doctrine:schema:validate --skip-sync
```

### Priorité 2: Nettoyer les orphelines (optionnel)
**Si la BD fonctionne correctement**, ignorer les 27 migrations orphelines. Elles ne causent pas de problème fonctionnels si les tables existent déjà.

### Priorité 3: Traiter les 2 migrations dupliquées
**AVANT d'appliquer:**
1. Vérifier si `Type_DEV` est vraiment nécessaire
2. Confirmer que les suppressions de tables sont intentionnelles
3. **Supprimer la duplication**: Garder seulement `Version20260326145648` et effacer `Version20260326145649`
4. Si les suppressions ne sont pas intentionnelles, **supprimer les deux fichiers**

### Priorité 4: Resynchroniser (si nécessaire)
```bash
# Générer une migration basée sur les différences
php bin/console make:migration --diff-against-db

# Appliquer les migrations en attente
php bin/console doctrine:migrations:migrate
```

---

## 📝 Synthèse des Chiffres

| Métrique | Valeur |
|----------|--------|
| Total migrations dans le système | 114 |
| Fichiers PHP existants | 62 |
| Migrations exécutées | 87 |
| Migrations orphelines (fichier disparu) | 27 |
| Migrations en attente | 2 |
| État du schéma | ❌ Désynchronisé |

---

## ⚠️ Points Critiques

1. **Migrations dupliquées** → Risque de confusion et d'exécution double
2. **Tables de volet** → Pourraient être supprimées accidentellement
3. **État du schéma** → Indicate des incohérences à corriger

---

## 📞 Prochaines Étapes

1. **Valider** les migrations en attente (especially les suppressions de tables)
2. **Supprimer** le fichier `Version20260326145649.php` (doublon)
3. **Exécuter** `php bin/console make:migration` pour voir les vrais changements nécessaires
4. **Tester** en environnement dev avant production

