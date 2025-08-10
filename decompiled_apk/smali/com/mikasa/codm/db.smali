.class Lcom/mikasa/codm/db;
.super Ljava/lang/Object;

# interfaces
.implements Landroid/view/View$OnFocusChangeListener;


# static fields
.field private static final short:[S


# instance fields
.field private final a:Lcom/mikasa/codm/da;


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0xc

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/db;->short:[S

    return-void

    :array_0
    .array-data 2
        0x9aas
        0x9ads
        0x9b3s
        0x9b6s
        0x9b7s
        0x99cs
        0x9aes
        0x9a6s
        0x9b7s
        0x9abs
        0x9acs
        0x9a7s
    .end array-data
.end method

.method native constructor <init>(Lcom/mikasa/codm/da;)V
.end method

.method public static native ۟ۥ۟۟ۢ(Ljava/lang/Object;)Landroid/content/Context;
.end method

.method public static native ۠ۢۥ۠()[S
.end method

.method public static native ۣۢ۠ۨ(Ljava/lang/Object;)Lcom/mikasa/codm/Menu;
.end method

.method public static native ۣ۠ۦ(Ljava/lang/Object;)Lcom/mikasa/codm/da;
.end method


# virtual methods
.method public native onFocusChange(Landroid/view/View;Z)V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method
