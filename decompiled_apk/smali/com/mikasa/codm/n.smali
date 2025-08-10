.class Lcom/mikasa/codm/n;
.super Ljava/lang/Object;

# interfaces
.implements Landroid/widget/RadioGroup$OnCheckedChangeListener;


# static fields
.field private static final short:[S


# instance fields
.field private final a:Lcom/mikasa/codm/MainActivity;


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0xa

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/n;->short:[S

    return-void

    :array_0
    .array-data 2
        0x26bs
        0x260s
        0x585s
        0x590s
        0x93ds
        0x925s
        0x248s
        0x251s
        0x5ccs
        0x5c1s
    .end array-data
.end method

.method native constructor <init>(Lcom/mikasa/codm/MainActivity;)V
.end method

.method public static native ۢۧۨۦ()[S
.end method

.method public static native ۦۧۡۧ(Ljava/lang/Object;)V
.end method


# virtual methods
.method public native onCheckedChanged(Landroid/widget/RadioGroup;I)V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method
